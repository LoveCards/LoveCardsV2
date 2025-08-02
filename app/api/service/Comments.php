<?php

namespace app\api\service;

use think\facade\Db;

use app\common\Common;

use app\api\model\Comments as CommentsModel;
use app\api\model\Cards as CardsModel;

use yunarch\app\api\service\IndexUtils;

class Comments
{
    //更新指定ID的指定字段
    static public function updata($context, $data, $where = [], $allowField = [])
    {
        $where = ['user_id' => $context['uid']] + $where;
        $result = CommentsModel::update($data, $where, $allowField);

        return $result;
    }

    //列表
    static public function list($context)
    {
        //$currentPage = 1;
        $pageSize = 15;

        $result = CommentsModel::where('status', 0)
            ->where('user_id', $context['uid'])
            ->order('id', 'desc')
            ->paginate($pageSize);

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
            CommentsModel::where('id', 'in', $ids)->update([$fields => Db::raw($sql)]);
            Db::commit(); // 提交事务
            return Common::mArrayEasyReturnStruct('更新成功', true);
        } catch (\Throwable $th) {
            Db::rollback(); // 回滚事务
            return Common::mArrayEasyReturnStruct('更新失败', false, $th->getMessage());
        }
    }

    static public function Index($params)
    {
        $index = new IndexUtils(CommentsModel::class, $params);
        $result = $index->common('content', [], true);
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result->toArray());
        }
        return Common::mArrayEasyReturnStruct('查询失败', false);
    }

    /**
     * 创建单张评论方法
     *
     * @param array $data 评论数据
     * @return void
     */
    static public function createComment($params)
    {
        $id = $params['id'];
        unset($params['id']);
        $params['aid'] = 1;
        $params['pid'] = $id;

        // 存储事务
        Db::startTrans();
        try {
            CommentsModel::create($params);
            CardsModel::where('id', $id)->where('status', 0)->inc('comments')->update();

            Db::commit(); // 提交事务
            return Common::mArrayEasyReturnStruct('创建成功', true);
        } catch (\Throwable $th) {
            Db::rollback(); // 回滚事务
            return Common::mArrayEasyReturnStruct('创建失败', false, $th->getMessage());
        }
    }

    /**
     * 更新单张评论方法
     *
     * @param array $data 评论数据
     * @return void
     */
    static public function updateComment($data)
    {
        // 存储事务
        Db::startTrans();
        try {
            CommentsModel::update($data);

            Db::commit(); // 提交事务
            return Common::mArrayEasyReturnStruct('更新成功', true);
        } catch (\Throwable $th) {
            Db::rollback(); // 回滚事务
            return Common::mArrayEasyReturnStruct('更新失败', false, $th->getMessage());
        }
    }

    /**
     * 批量操作评论
     *
     * @param string $method top：置顶|ban：状态封禁仅自己可见|approve：状态待审核仅自己可见|hide：状态隐藏仅后台可见|delete：删除
     * @param array $ids
     * @return void
     */
    static public function batchOperate($method, $ids)
    {
        switch ($method) {
            case 'top':
                return self::fieldsToggle('is_top', $ids, [0, 1]);
            case 'approve':
                return self::fieldsToggle('status', $ids, [0, 3], [1, 2]);
            case 'ban':
                return self::fieldsToggle('status', $ids, [0, 1], [2, 3]);
            case 'hide':
                return self::fieldsToggle('status', $ids, [0, 2], [1, 3]);
            case 'delete':
                return self::deleteComments(false, $ids);
            default:
                return Common::mArrayEasyReturnStruct('方法不存在', false);
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
    static public function deleteComments($id = false, $ids = [])
    {
        $data = $id ? $id : $ids;
        // 存储事务
        Db::startTrans();
        try {
            CommentsModel::destroy($data);

            Db::commit(); // 提交事务
            return Common::mArrayEasyReturnStruct('删除成功', true);
        } catch (\Throwable $th) {
            Db::rollback(); // 回滚事务
            return Common::mArrayEasyReturnStruct('删除失败', false, $th->getMessage());
        }
    }
}
