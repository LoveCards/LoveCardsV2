<?php

namespace app\api\service;

use app\api\model\Likes as LikesModel;
use app\api\model\Cards as CardsModel;

use think\db\Query;

class Likes
{

    //列表
    static public function list()
    {
        $context = request()->JwtData;
        if ($context['uid'] == 0) {
            throw new \Exception('请先登入', 401);
        }

        //$currentPage = 1;
        $pageSize = 15;

        $result = LikesModel::where('uid', $context['uid'])->paginate($pageSize);
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

    static public function delete($data)
    {
        $context = request()->JwtData;
        if ($context['uid'] == 0) {
            throw new \Exception('请先登入', 401);
        }

        if (is_array($data)) {
            $result = LikesModel::whereIn('id', $data)->where('uid', $context['uid'])->delete();
        } else {
            $result = LikesModel::where('id', $data)->where('uid', $context['uid'])->delete();
        }

        return $result;
    }
}
