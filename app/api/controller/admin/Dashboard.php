<?php

namespace app\api\controller\admin;

use think\facade\Db;

use app\api\model\Cards as CardsModel;
use app\api\model\Likes as LikesModel;
use app\api\model\Comments as CommentsModel;

use app\common\Export;


class Dashboard
{

    //列表
    public function Index()
    {
        $context = request()->JwtData;
        if ($context['uid'] == 0) {
            return Export::Create([], 401, '请先登入');
        }

        //函数-取图表数据
        function fArrayGetChartData($key)
        {
            for ($i = 1; $i <= 6; $i++) {
                $time = date('Y-m-d', strtotime('-' . $i . 'day'));
                $arr['y'][$i] = Db::table($key)->whereDay('created_at', $time)->count();
                $arr['x'][$i] = $time;
                if ($i == 1) $arr['x'][$i] = '昨日';
            }
            $arr['y'] = Array_reverse($arr['y']);
            $arr['x'] = Array_reverse($arr['x']);
            return $arr;
        }

        //取总览数据
        $tDef_ViewDataCount = [
            'cards' => CardsModel::count(),
            'comments' => CommentsModel::count(),
            'good' => LikesModel::count()
        ];
        //取图表数据
        $tDef_ViewChartJson = [
            [
                'label' => '卡片',
                'data' => fArrayGetChartData('cards'),
            ],
            [
                'label' => '评论',
                'data' => fArrayGetChartData('comments'),
            ],
            [
                'label' => '点赞',
                'data' => [], //fArrayGetChartData('goods')
            ],
        ];

        $result = [
            'count' => $tDef_ViewDataCount,
            'chart' => $tDef_ViewChartJson,
        ];

        return Export::Create($result);
    }
}
