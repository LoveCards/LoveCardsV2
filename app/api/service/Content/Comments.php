<?php

namespace app\api\service\Content;

use think\facade\Db;

use app\api\model\Comments as CommentsModel;
use app\api\model\Cards as CardsModel;

use yunarch\utils\src\ModelList;

class Comments
{
    static public function update(int $uid, $data, $where = [], $allowField = [])
    {
        $where = ['user_id' => $uid] + $where;
        $result = CommentsModel::update($data, $where, $allowField);

        return $result;
    }

    static public function fieldsToggle($fields, $ids, $value1 = [0, 1], $value2 = false): void
    {
        $where = "WHEN {$fields} = {$value1[0]} THEN {$value1[1]} WHEN {$fields} = {$value1[1]} THEN {$value1[0]} ";
        if ($value2) {
            foreach ($value2 as $item) {
                $where = $where . "WHEN {$fields} = {$item} THEN {$value1[1]} ";
            }
        }
        $sql = "CASE {$where}END";
        CommentsModel::where('id', 'in', $ids)->update([$fields => Db::raw($sql)]);
    }

    public static function newList(array $params, int $user_id = -1): array
    {
        $params['search_default_key'] = 'content';
        if ($user_id != -1) {
            $params['where'] = [
                'status' => [0, 1, 3],
                'user_id' => $user_id
            ];
        }
        $result = ModelList::make(CommentsModel::class)->getPaginate($params);

        return $result->toArray();
    }

    public static function listAll(array $params): array
    {
        $params['search_default_key'] = 'content';
        $result = ModelList::make(CommentsModel::class)->getPaginate($params);

        return $result->toArray();
    }

    public static function get(int $id): array
    {
        $result = CommentsModel::where('id', $id)->findOrEmpty();
        if ($result->isEmpty()) {
            throw \app\api\ApiException::notFound('评论不存在', \app\api\ApiException::CODE_RESOURCE_NOT_FOUND);
        }
        return $result->toArray();
    }

    public static function getAny(int $id): array
    {
        return self::get($id);
    }

    public static function updateAny($data): void
    {
        CommentsModel::update($data);
    }

    static public function createComment($params): array
    {
        $id = $params['id'];
        unset($params['id']);
        $params['aid'] = 1;
        $params['pid'] = $id;

        Db::startTrans();
        try {
            $comment = CommentsModel::create($params);
            CardsModel::where('id', $id)->where('status', 0)->inc('comments')->update();

            Db::commit();
            return ['data' => $comment];
        } catch (\Throwable $th) {
            Db::rollback();
            throw \app\api\ApiException::error('创建失败', \app\api\ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    static public function updateComment($data): void
    {
        CommentsModel::update($data);
    }

    static public function batchOperate($method, $ids): void
    {
        switch ($method) {
            case 'top':
                self::fieldsToggle('is_top', $ids, [0, 1]);
                break;
            case 'approve':
                self::fieldsToggle('status', $ids, [0, 3], [1, 2]);
                break;
            case 'ban':
                self::fieldsToggle('status', $ids, [0, 1], [2, 3]);
                break;
            case 'hide':
                self::fieldsToggle('status', $ids, [0, 2], [1, 3]);
                break;
            case 'delete':
                self::deleteComments(false, $ids);
                break;
            default:
                throw \app\api\ApiException::badRequest('方法不存在', \app\api\ApiException::CODE_PARAM_INVALID);
        }
    }

    static public function deleteComments($id = false, $ids = []): void
    {
        $data = $id ? $id : $ids;
        CommentsModel::destroy($data);
    }

    static public function deleteAny($id = false, $ids = []): void
    {
        self::deleteComments($id, $ids);
    }
}
