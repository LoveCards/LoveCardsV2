<?php

namespace app\api\service;

use think\facade\Db;

use app\api\model\Cards as CardsModel;
use app\api\model\TagsMap as TagsMapModel;
use app\api\model\Images as ImagesModel;
use app\api\model\Comments as CommentsModel;

use yunarch\utils\src\ModelList;

class Cards
{
    /**
     * 热门卡片列表
     *
     * @return array
     */
    public static function hotList(): array
    {
        define("CONST_G_TOP_LISTS_MAX", 32); //置顶卡片列表最大个数
        define("CONST_G_HOT_LISTS_MAX", 8); //热门卡片列表最大个数

        //查询数据
        $lDef_Result = Db::table('cards')
            ->where('status', 0)
            ->where('is_top', 1)
            ->where('deleted_at', null)
            ->order('id', 'desc')
            ->limit(CONST_G_TOP_LISTS_MAX)
            ->select()->toArray();
        $lDef_CardLists = $lDef_Result;

        $lDef_Result = Db::query("select * from cards where is_top = 0 and status = 0 and deleted_at IS NULL order by comments*0.3+good*0.7 desc limit 0," . CONST_G_HOT_LISTS_MAX);

        $lDef_CardLists = array_merge($lDef_CardLists, $lDef_Result);

        return $lDef_CardLists;
    }

    /**
     * 搜索列表
     *
     * @param array $params [search_default_key,ModelList[],where[]]
     * @param integer $user_id 用户id
     * @return array
     */
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

    /**
     * 批量操作
     *
     * @param string $method top：置顶|1ban：状态封禁仅自己可见|2approve：状态待审核仅自己可见|3hide：状态隐藏仅后台可见|delete：删除
     * @param array $ids [1,2,3]
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
                self::deleteCards(false, $ids);
                break;
            default:
                throw \app\api\ApiException::createBadRequest('方法不存在', []);
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
        CardsModel::where('id', 'in', $ids)->update([$fields => Db::raw($sql)]);
    }

    /**
     * 创建一条数据
     *
     * @param array $data 卡片数据
     * @return string id 返回创建ID
     */
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
            throw \app\api\ApiException::createError('创建失败', null, $th);
        }
    }

    /**
     * 更新一条数据
     *
     * @param array $data [Cards[]]
     * @return void
     */
    static public function updateCard($data): void
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
        } catch (\Throwable $th) {
            Db::rollback(); // 回滚事务
            throw \app\api\ApiException::createError('更新失败', null, $th);
        }
    }
    /**
     * 更新/创建一条数据标签
     *
     * @param array $data
     * @param boolean $create
     * @return void
     */
    static public function updateCardTags($data, $create = false): void
    {
        $pid = (int) $data['id'];
        $tags = json_decode($data['tags'], true);

        if (!$create) {
            // 删除旧的标签映射
            TagsMapModel::where('aid', 1)->where('pid', $pid)->delete();
        }

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
    /**
     * 更新/创建一条数据图集
     *
     * @param array $data
     * @param boolean $create
     * @return void
     */
    static public function updateCardPictures($data, $create = false): void
    {
        $pid = (int) $data['id'];
        $pictures = json_decode($data['pictures'], true);

        if (!$create) {
            // 解绑旧的图片
            $def_data = [
                'aid' => 0, // 模块ID
                'pid' => 0, // 卡片ID
            ];
            ImagesModel::where('aid', 1)->where('pid', $pid)->update($def_data);
        }
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
     * 删除单&多条数据
     * * 删除卡片时会同时删除相关的标签、图片和评论
     *
     * @param boolean $id 一条数据ID
     * @param array $ids 多条数据ID集
     * @return void
     */
    static public function deleteCards($id = false, $ids = []): void
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
        } catch (\Throwable $th) {
            Db::rollback(); // 回滚事务
            throw \app\api\ApiException::createError('删除失败', null, $th);
        }
    }
    /**
     * 删除单&多条数据标签
     *
     * @param array $pids [1,2,3]
     * @return void
     */
    static public function deleteCardsTags($pids): void
    {
        TagsMapModel::where('aid', 1)->where('pid', 'in', $pids)->delete();
    }
    /**
     * 解绑单&多条数据图片
     *
     * @param array $pids [1,2,3]
     * @return void
     */
    static public function deleteCardsPictures($pids): void
    {
        $def_data = [
            'aid' => 0,
            'pid' => 0,
        ];
        ImagesModel::where('aid', 1)->where('pid', 'in', $pids)->update($def_data);
    }
    /**
     * 删除单&多条数据评论
     *
     * @param array $pids [1,2,3]
     * @return void
     */
    static public function deleteCardsComments($pids): void
    {
        CommentsModel::where('aid', 1)->where('pid', 'in', $pids)->delete();
    }
}
