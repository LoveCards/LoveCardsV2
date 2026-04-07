<?php

namespace app\api\service;

use think\facade\Db;

use app\api\model\Comments as CommentsModel;
use app\api\model\Cards as CardsModel;

use yunarch\utils\src\ModelList;

class Comments
{
    //更新指定ID的指定字段
    static public function updata($context, $data, $where = [], $allowField = [])
    {
        $where = ['user_id' => $context['uid']] + $where;
        $result = CommentsModel::update($data, $where, $allowField);

        return $result;
    }

    /**
     * 字段反转
     *
     * @param string $fields 字段名
     * @param array $ids ID集
     * @param array $value1 反转值
     * @param array $value2 其他值 比如选项是1 2 3 4那么想要反转3,4那v2就填1,2
     * @return void
     */
    static public function fieldsToggle($fields, $ids, $value1 = [0, 1], $value2 = false): void
    {
        //生成命令
        $where = "WHEN {$fields} = {$value1[0]} THEN {$value1[1]} WHEN {$fields} = {$value1[1]} THEN {$value1[0]} ";
        if ($value2) {
            foreach ($value2 as $item) {
                $where = $where . "WHEN {$fields} = {$item} THEN {$value1[1]} ";
            }
        }
        $sql = "CASE {$where}END";
        //执行
        CommentsModel::where('id', 'in', $ids)->update([$fields => Db::raw($sql)]);
    }

    /**
     * 搜索列表
     *
     * @param array $params [search_default_key,ModelList[],where[]]
     * @param integer $user_id 用户id
     * @return array
     */
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

    /**
     * 创建单张评论方法
     *
     * @param array $data 评论数据
     * @return array
     */
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

    /**
     * 更新单张评论方法
     *
     * @param array $data 评论数据
     * @return void
     */
    static public function updateComment($data): void
    {
        CommentsModel::update($data);
    }

    /**
     * 批量操作评论
     *
     * @param string $method top：置顶|ban：状态封禁仅自己可见|approve：状态待审核仅自己可见|hide：状态隐藏仅后台可见|delete：删除
     * @param array $ids
     * @return void
     */
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

    /**
     * 删除单&多张评论方法
     * * 删除评论时会同时删除相关的标签、图片和评论
     *
     * @param boolean $id 单张评论ID
     * @param array $ids 多张评论ID集
     * @return void
     */
    static public function deleteComments($id = false, $ids = []): void
    {
        $data = $id ? $id : $ids;
        CommentsModel::destroy($data);
    }
}
