<?php

namespace app\api\service;

use think\facade\Db;
use app\common\Common;

use app\api\model\Tags as TagsModel;
use app\api\model\TagsMap as TagsMapModel;

use yunarch\utils\src\ModelList;

class Tags
{

    /**
     * 字段反转
     *
     * @param string $fields 字段名
     * @param array $ids ID集
     * @param array $value1 反转值
     * @param array $value2 其他值 比如选项是1 2 3 4那么想要反转3,4那v2就填1,2
     * @return void
     */
    static public function fieldsToggle($fields, $ids, $value1 = [0, 1], $value2 = false)
    {
        //生成命令
        $where = "WHEN {$fields} = {$value1[0]} THEN {$value1[1]} WHEN {$fields} = {$value1[1]} THEN {$value1[0]} ";
        if ($value2) {
            foreach ($value2 as $item) {
                $where = $where . "WHEN {$fields} = {$item} THEN {$value1[1]} ";
            }
        }
        $sql = "CASE {$where}END";

        TagsModel::where('id', 'in', $ids)->update([$fields => Db::raw($sql)]);
    }

    /**
     * 读取全部标签列表
     *
     * @return array
     */
    static public function noPaginateIndex($params): array
    {
        $params['search_default_key'] = 'name';
        $result = ModelList::make(TagsModel::class)->getNoPaginate($params);
        return $result->toArray();
    }

    /**
     * 读取全部标签列表
     *
     * @return array
     */
    static public function Index($params): array
    {
        $params['search_default_key'] = 'name';
        $result = ModelList::make(TagsModel::class)->getPaginate($params);
        return $result->toArray();
    }

    /**
     * 创建标签
     *
     * @param array $params
     * @return void
     */
    static public function createTag($params): void
    {
        $params['aid'] = 1;
        TagsModel::create($params);
    }

    /**
     * 批量操作标签
     *
     * @param string $method top：置顶|ban：状态封禁仅自己可见|approve：状态待审核仅自己可见|hide：状态隐藏仅后台可见|delete：删除
     * @param array $ids
     * @return void
     */
    static public function batchOperate($method, $ids): void
    {
        switch ($method) {
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
                self::deleteTags(false, $ids);
                break;
            default:
                throw \app\api\ApiException::badRequest('方法不存在', \app\api\ApiException::CODE_PARAM_INVALID);
        }
    }


    /**
     * 删除单&多条数据方法
     * * 删除标签时会同时删除关联
     *
     * @param boolean $id 单条数据ID
     * @param array $ids 多条数据ID集
     * @return void
     */
    static public function deleteTags($id = false, $ids = []): void
    {
        $data = $id ? $id : $ids;
        // 存储事务
        Db::startTrans();
        try {
            self::deleteTagsMap($data);

            TagsModel::destroy($data);

            Db::commit(); // 提交事务
        } catch (\Throwable $th) {
            Db::rollback(); // 回滚事务
            throw \app\api\ApiException::error('删除失败', \app\api\ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }
    /**
     * 删除单&多个数据关联
     *
     * @param array $ids
     * @return void
     */
    static public function deleteTagsMap(array $ids): void
    {
        TagsMapModel::where('id', 'in', $ids)->delete();
    }

    /**
     * 更新单条数据方法
     *
     * @param array $data 标签数据
     * @return void
     */
    static public function updateTag(array $data): TagsModel
    {
        return TagsModel::update($data);
    }
}
