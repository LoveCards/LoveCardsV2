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

    //列表
    static public function list()
    {
        $context = request()->JwtData;

        //$currentPage = 1;
        $pageSize = 15;

        $result = CardsModel::where('status', 0)
            ->where('uid', $context['uid'])
            ->order('id', 'desc')
            ->paginate($pageSize);

        return $result;
    }

    //模型更新方法
    static public function updata($data, $where = [], $allowField = [])
    {
        return CardsModel::update($data, $where, $allowField);
    }

    //卡片更新方法
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
    //更新卡片标签
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
    //更新卡片图片
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

    //删除卡片方法
    static public function deleteCard($data)
    {
        // 存储事务
        Db::startTrans();
        try {
            self::deleteCardTags($data);
            self::deleteCardPictures($data);
            self::deleteCardComments($data);

            CardsModel::delete($data['id']);

            Db::commit(); // 提交事务
            return Common::mArrayEasyReturnStruct('删除成功', true);
        } catch (\Throwable $th) {
            Db::rollback(); // 回滚事务
            return Common::mArrayEasyReturnStruct('删除失败', false, $th->getMessage());
        }
    }
    //删除卡片标签
    static public function deleteCardTags($data)
    {
        $pid = (int) $data['id'];
        TagsMapModel::where('aid', 1)->where('pid', $pid)->delete();
    }
    //删除卡片图片
    static public function deleteCardPictures($data)
    {
        $pid = (int) $data['id'];
        $def_data = [
            'aid' => 0,
            'pid' => 0,
        ];
        ImagesModel::where('aid', 1)->where('pid', $pid)->update($def_data);
    }
    //删除卡片评论
    static public function deleteCardComments($data)
    {
        $pid = (int) $data['id'];
        CommentsModel::where('aid', 1)->where('pid', $pid)->delete();
    }
}
