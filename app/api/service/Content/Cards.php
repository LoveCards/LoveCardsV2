<?php

namespace app\api\service\Content;

use think\facade\Db;

use app\api\model\Cards as CardsModel;
use app\api\model\TagsMap as TagsMapModel;
use app\api\model\Images as ImagesModel;
use app\api\model\Comments as CommentsModel;

use yunarch\utils\src\ModelList;

class Cards
{
    const TOP_LISTS_MAX = 32;
    const HOT_LISTS_MAX = 8;

    public static function hotList(): array
    {
        $lDef_Result = Db::table('cards')
            ->where('status', 0)
            ->where('is_top', 1)
            ->where('deleted_at', null)
            ->order('id', 'desc')
            ->limit(self::TOP_LISTS_MAX)
            ->select()->toArray();
        $lDef_CardLists = $lDef_Result;

        $lDef_Result = Db::query("select * from cards where is_top = 0 and status = 0 and deleted_at IS NULL order by comments*0.3+goods*0.7 desc limit 0," . self::HOT_LISTS_MAX);

        $lDef_CardLists = array_merge($lDef_CardLists, $lDef_Result);

        return $lDef_CardLists;
    }

    public static function list(array $params, int $user_id = -1): array
    {
        $params['search_default_key'] = 'content';
        if ($user_id != -1) {
            $params['where'] = [
                'status' => [0, 1, 3],
                'user_id' => $user_id
            ];
        }
        $result = ModelList::make(CardsModel::class)->getPaginate($params);

        return $result->toArray();
    }

    public static function get(int $id): array
    {
        $result = CardsModel::where('id', $id)->where('status', 0)->findOrEmpty();
        if ($result->isEmpty()) {
            throw \app\api\ApiException::notFound('卡片不存在', \app\api\ApiException::CODE_RESOURCE_NOT_FOUND);
        }
        return $result->toArray();
    }

    public static function getAny(int $id): array
    {
        $result = CardsModel::where('id', $id)->findOrEmpty();
        if ($result->isEmpty()) {
            throw \app\api\ApiException::notFound('卡片不存在', \app\api\ApiException::CODE_RESOURCE_NOT_FOUND);
        }
        return $result->toArray();
    }

    public static function listOwn(array $params, int $uid): array
    {
        $params['search_default_key'] = 'content';
        $params['where'] = [
            'user_id' => $uid
        ];
        $result = ModelList::make(CardsModel::class)->getPaginate($params);

        return $result->toArray();
    }

    public static function listAll(array $params): array
    {
        $params['search_default_key'] = 'content';
        $result = ModelList::make(CardsModel::class)->getPaginate($params);

        return $result->toArray();
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
                self::deleteCards(false, $ids);
                break;
            default:
                throw \app\api\ApiException::badRequest('方法不存在', \app\api\ApiException::CODE_PARAM_INVALID);
        }
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
        CardsModel::where('id', 'in', $ids)->update([$fields => Db::raw($sql)]);
    }

    static public function createCard($data): string
    {
        Db::startTrans();
        try {
            $result = CardsModel::create($data);
            $data['id'] = $result->id;
            if (isset($data['tags'])) {
                self::updateCardTags($data, true);
            }
            if (isset($data['pictures'])) {
                self::updateCardPictures($data, true);
                unset($data['pictures']);
            }
            Db::commit();
            return $result->id;
        } catch (\Throwable $th) {
            Db::rollback();
            throw \app\api\ApiException::error('创建失败', \app\api\ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    static public function updateCard($data): void
    {
        Db::startTrans();
        try {
            if (isset($data['tags'])) {
                self::updateCardTags($data);
            }
            if (isset($data['pictures'])) {
                self::updateCardPictures($data);
                unset($data['pictures']);
            }

            CardsModel::update($data);

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            throw \app\api\ApiException::error('更新失败', \app\api\ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    static public function updateCardTags($data, $create = false): void
    {
        $pid = (int) $data['id'];
        $tags = json_decode($data['tags'], true);

        if (!$create) {
            TagsMapModel::where('aid', 1)->where('pid', $pid)->delete();
        }

        foreach ($tags as $tag_id) {
            $item = [
                'aid' => 1,
                'pid' => $pid,
                'tag_id' => $tag_id,
            ];
            TagsMapModel::create($item);
        }
    }

    static public function updateCardPictures($data, $create = false): void
    {
        $pid = (int) $data['id'];
        $pictures = json_decode($data['pictures'], true);

        if (!$create) {
            $def_data = [
                'aid' => 0,
                'pid' => 0,
            ];
            ImagesModel::where('aid', 1)->where('pid', $pid)->update($def_data);
        }
        foreach ($pictures as $picture_id) {
            $item = [
                'id' => $picture_id,
                'aid' => 1,
                'pid' => $pid,
            ];
            ImagesModel::update($item);
        }
    }

    static public function deleteCards($id = false, $ids = []): void
    {
        $data = $id ? $id : $ids;
        Db::startTrans();
        try {
            self::deleteCardsTags($data);
            self::deleteCardsPictures($data);
            self::deleteCardsComments($data);

            CardsModel::destroy($data);

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            throw \app\api\ApiException::error('删除失败', \app\api\ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    static public function deleteCardsTags($pids): void
    {
        TagsMapModel::where('aid', 1)->where('pid', 'in', $pids)->delete();
    }

    static public function deleteCardsPictures($pids): void
    {
        $def_data = [
            'aid' => 0,
            'pid' => 0,
        ];
        ImagesModel::where('aid', 1)->where('pid', 'in', $pids)->update($def_data);
    }

    static public function deleteCardsComments($pids): void
    {
        CommentsModel::where('aid', 1)->where('pid', 'in', $pids)->delete();
    }

    static public function updateAny($data): void
    {
        self::updateCard($data);
    }

    static public function deleteAny($id = false, $ids = []): void
    {
        self::deleteCards($id, $ids);
    }
}
