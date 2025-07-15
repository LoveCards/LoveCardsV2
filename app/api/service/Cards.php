<?php

namespace app\api\service;

use think\facade\Db;

use app\common\Common;

use app\api\model\Cards as CardsModel;
use app\api\model\TagsMap as TagsMapModel;
use app\api\model\Images as ImagesModel;
use app\api\model\Comments as CommentsModel;

use yunarch\app\api\service\IndexUtils;

class Cards
{

    /**
     * 读取用户卡片
     *
     * @return void
     */
    static public function Index($params)
    {
        $index = new IndexUtils(CardsModel::class, $params);
        $result = $index->common('content', [], true);
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result->toArray());
        }
        return Common::mArrayEasyReturnStruct('查询失败', false);
    }

    /**
     * 读取卡片
     *
     * @return void
     */
    static public function Get($id)
    {
        $result = CardsModel::where('id', $id)->find();
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result->toArray());
        }
        return Common::mArrayEasyReturnStruct('查询失败', false);
    }

    //列表-旧的
    // static public function list()
    // {
    //     $context = request()->JwtData;

    //     //$currentPage = 1;
    //     $pageSize = 15;

    //     $result = CardsModel::where('status', 0)
    //         ->where('uid', $context['uid'])
    //         ->order('id', 'desc')
    //         ->paginate($pageSize);

    //     return $result;
    // }

    //模型更新方法
    static public function updata($data, $where = [], $allowField = [])
    {
        return CardsModel::update($data, $where, $allowField);
    }

    /**
     * 批量操作卡片
     *
     * @param string $method top：置顶|1ban：状态封禁仅自己可见|2approve：状态待审核仅自己可见|3hide：状态隐藏仅后台可见|delete：删除
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
                return self::deleteCards(false, $ids);
            default:
                return Common::mArrayEasyReturnStruct('方法不存在', false);
        }
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
            CardsModel::where('id', 'in', $ids)->update([$fields => Db::raw($sql)]);
            Db::commit(); // 提交事务
            return Common::mArrayEasyReturnStruct('更新成功', true);
        } catch (\Throwable $th) {
            Db::rollback(); // 回滚事务
            return Common::mArrayEasyReturnStruct('更新失败', false, $th->getMessage());
        }
    }

    /**
     * 更新单张卡片方法
     *
     * @param array $data 卡片数据
     * @return void
     */
    static public function updateCard($data)
    {
        // 存储事务
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

            Db::commit(); // 提交事务
            return Common::mArrayEasyReturnStruct('更新成功', true);
        } catch (\Throwable $th) {
            Db::rollback(); // 回滚事务
            return Common::mArrayEasyReturnStruct('更新失败', false, $th->getMessage());
        }
    }
    //更新单张卡片标签
    static public function updateCardTags($data)
    {
        $pid = (int) $data['id'];
        $tags = json_decode($data['tags'], true);

        // 删除旧的标签映射
        TagsMapModel::where('aid', 1)->where('pid', $pid)->delete();

        // 创建新的标签映射
        foreach ($tags as $tag_id) {
            $item = [
                'aid' => 1, // 模块ID
                'pid' => $pid, // 卡片ID
                'tag_id' => $tag_id, // 标签ID
            ];
            TagsMapModel::create($item);
        }
    }
    //更新单张卡片图片
    static public function updateCardPictures($data)
    {
        $pid = (int) $data['id'];
        $pictures = json_decode($data['pictures'], true);

        // 解绑旧的图片
        $def_data = [
            'aid' => 0, // 模块ID
            'pid' => 0, // 卡片ID
        ];
        ImagesModel::where('aid', 1)->where('pid', $pid)->update($def_data);

        // 批量绑定图片到卡片
        foreach ($pictures as $picture_id) {
            $item = [
                'id' => $picture_id, // 图片ID
                'aid' => 1, // 模块ID
                'pid' => $pid, // 卡片ID
            ];
            ImagesModel::update($item);
        }
    }

    /**
     * 删除单&多张卡片方法
     * * 删除卡片时会同时删除相关的标签、图片和评论
     *
     * @param boolean $id 单张卡片ID
     * @param array $ids 多张卡片ID集
     * @return void
     */
    static public function deleteCards($id = false, $ids = [])
    {
        $data = $id ? $id : $ids;
        // 存储事务
        Db::startTrans();
        try {
            self::deleteCardsTags($data);
            self::deleteCardsPictures($data);
            self::deleteCardsComments($data);

            CardsModel::destroy($data);

            Db::commit(); // 提交事务
            return Common::mArrayEasyReturnStruct('删除成功', true);
        } catch (\Throwable $th) {
            Db::rollback(); // 回滚事务
            return Common::mArrayEasyReturnStruct('删除失败', false, $th->getMessage());
        }
    }
    //删除单&多张卡片标签
    static public function deleteCardsTags($pids)
    {
        TagsMapModel::where('aid', 1)->where('pid', 'in', $pids)->delete();
    }
    //解绑单&多张卡片图片
    static public function deleteCardsPictures($pids)
    {
        $def_data = [
            'aid' => 0,
            'pid' => 0,
        ];
        ImagesModel::where('aid', 1)->where('pid', 'in', $pids)->update($def_data);
    }
    //删除单&多张卡片评论
    static public function deleteCardsComments($pids)
    {
        CommentsModel::where('aid', 1)->where('pid', 'in', $pids)->delete();
    }
}
