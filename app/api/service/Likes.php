<?php

namespace app\api\service;

use app\api\model\Likes as LikesModel;
use app\api\model\Cards as CardsModel;

use \think\facade\Db;
use think\db\Query;

class Likes
{

    /**
     * 列表
     *
     * @param array $context
     * @return array
     */
    static public function list($context)
    {
        //$currentPage = 1;
        $pageSize = 15;

        $result = LikesModel::where('uid', $context['uid'])->paginate($pageSize);
        if($result->isEmpty()){
            throw new \Exception('没有找到', 204);
        }

        $likes = $result->toArray();
        foreach ($result as $items) {
            $apps[$items['aid']][] = $items['pid'];
        }

        $result = CardsModel::where('status', 0)->select($apps[1])->toArray();

        //合并数组
        function matchAndInsert($array1, $array2, $key1, $key2, $insertKey)
        {
            foreach ($array1 as &$item1) {
                foreach ($array2 as $item2) {
                    if ($item1[$key1] == $item2[$key2]) {
                        $item1[$insertKey] = $item2;
                        break;
                    } else {
                        $item1[$insertKey] = '';
                    }
                }
            }
            return $array1;
        }
        $likes['data'] = matchAndInsert($likes['data'], $result, 'pid', 'id', 'card');

        return $likes;
    }

    /**
     * 删除喜欢
     *
     * @param int|array $data
     * @param array $context
     * @return void
     */
    static public function delete($data, $context)
    {
        // 启动事务
        Db::startTrans();
        try {
            if (is_array($data)) {
                //批量
                $Likes = LikesModel::whereIn('id', $data)->where('uid', $context['uid']);
                $card_pids = $Likes->column('pid');
                $Likes->delete();
                CardsModel::whereIn('id', $card_pids)->dec('good')->update();
            } else {
                $Likes = LikesModel::where('id', $data)->where('uid', $context['uid']);
                $card_pids = $Likes->column('pid');
                $Likes->delete();
                CardsModel::where('id', $card_pids[0])->dec('good')->update();
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return $e;
        }
    }
}
