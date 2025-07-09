<?php

namespace app\api\service;

use think\facade\Db;
use app\common\Common;

use app\api\model\Tags as TagsModel;
use app\api\model\TagsMap as TagsMapModel;

use yunarch\app\api\service\IndexUtils;

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
        // 存储事务
        Db::startTrans();
        try {
            TagsModel::where('id', 'in', $ids)->update([$fields => Db::raw($sql)]);
            Db::commit(); // 提交事务
            return Common::mArrayEasyReturnStruct('更新成功', true);
        } catch (\Throwable $th) {
            Db::rollback(); // 回滚事务
            return Common::mArrayEasyReturnStruct('更新失败', false, $th->getMessage());
        }
    }

    /**
     * 读取全部标签列表
     *
     * @return void
     */
    static public function noPaginateIndex($params)
    {
        $index = new IndexUtils(TagsModel::class, $params);
        $result = $index->noPaginate('name', [], true);
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result->toArray());
        }
        return Common::mArrayEasyReturnStruct('列表查询失败', false);
    }

    /**
     * 读取全部标签列表
     *
     * @return void
     */
    static public function Index($params)
    {
        $index = new IndexUtils(TagsModel::class, $params);
        $result = $index->common('name', [], true);
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result->toArray());
        }
        return Common::mArrayEasyReturnStruct('列表查询失败', false);
    }

    /**
     * 创建标签
     *
     * @param array $params
     * @return void
     */
    static public function createTag($params)
    {
        $params['aid'] = 1;

        try {
            TagsModel::create($params);
            return Common::mArrayEasyReturnStruct('创建成功', true);
        } catch (\Throwable $th) {
            return Common::mArrayEasyReturnStruct('创建失败', false, $th->getMessage());
        }
    }

    /**
     * 批量操作标签
     *
     * @param string $method top：置顶|ban：状态封禁仅自己可见|approve：状态待审核仅自己可见|hide：状态隐藏仅后台可见|delete：删除
     * @param array $ids
     * @return void
     */
    static public function batchOperate($method, $ids)
    {
        switch ($method) {
            case 'approve':
                return self::fieldsToggle('status', $ids, [0, 3], [1, 2]);
            case 'ban':
                return self::fieldsToggle('status', $ids, [0, 1], [2, 3]);
            case 'hide':
                return self::fieldsToggle('status', $ids, [0, 2], [1, 3]);
            case 'delete':
                return self::deleteTags(false, $ids);
            default:
                return Common::mArrayEasyReturnStruct('方法不存在', false);
        }
    }


    /**
     * 删除单&多张标签方法
     * * 删除标签时会同时删除关联
     *
     * @param boolean $id 单张标签ID
     * @param array $ids 多张标签ID集
     * @return void
     */
    static public function deleteTags($id = false, $ids = [])
    {
        $data = $id ? $id : $ids;
        // 存储事务
        Db::startTrans();
        try {
            self::deleteTagsMap($data);

            TagsModel::destroy($data);

            Db::commit(); // 提交事务
            return Common::mArrayEasyReturnStruct('删除成功', true);
        } catch (\Throwable $th) {
            Db::rollback(); // 回滚事务
            return Common::mArrayEasyReturnStruct('删除失败', false, $th->getMessage());
        }
    }
    //删除单&多个标签关联
    static public function deleteTagsMap($ids)
    {
        TagsMapModel::where('id', 'in', $ids)->delete();
    }

    /**
     * 更新单张标签方法
     *
     * @param array $data 标签数据
     * @return void
     */
    static public function updateTag($data)
    {
        try {
            TagsModel::update($data);
            return Common::mArrayEasyReturnStruct('更新成功', true);
        } catch (\Throwable $th) {
            return Common::mArrayEasyReturnStruct('更新失败', false, $th->getMessage());
        }
    }
}
