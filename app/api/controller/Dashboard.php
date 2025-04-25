<?php

namespace app\api\controller;

use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Config;

use app\api\model\Images as ImagesModel;

use app\api\service\Likes as LikesService;

use app\common\Common;
use app\common\Export;
use app\common\BackEnd;
use app\common\App;

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
                $arr['y'][$i] = Db::table($key)->whereDay('time', $time)->count();
                $arr['x'][$i] = $time;
                if ($i == 1) $arr['x'][$i] = '昨日';
            }
            $arr['y'] = Array_reverse($arr['y']);
            $arr['x'] = Array_reverse($arr['x']);
            return $arr;
        }

        //取总览数据
        $tDef_ViewDataCount = [
            'cards' => Db::table('cards')->count(),
            'comments' => Db::table('comments')->count(),
            'good' => Db::table('good')->count()
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
                'data' => fArrayGetChartData('good')
            ],
        ];

        $result = [
            'count' => $tDef_ViewDataCount,
            'chart' => $tDef_ViewChartJson,
        ];

        return Export::Create($result);
    }
}
