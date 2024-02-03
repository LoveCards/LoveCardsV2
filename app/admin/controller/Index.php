<?php

namespace app\admin\controller;

use think\facade\View;
use think\facade\Db;

use app\common\Common;
use app\admin\BaseController;

class Index extends BaseController
{

    //Index
    public function Index()
    {
        //函数-取图表数据
        function fArrayGetChartData($key)
        {
            for ($i = 1; $i <= 6; $i++) {
                $time = date('Y-m-d', strtotime('-' . $i . 'day'));
                $arr['count'][$i] = Db::table($key)->whereDay('time', $time)->count();
                $arr['date'][$i] = $time;
                if ($i == 1) $arr['date'][$i] = '昨日';
            }
            $arr['count'] = Array_reverse($arr['count']);
            $arr['date'] = Array_reverse($arr['date']);
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

        View::assign([
            'ViewDataCount' => $tDef_ViewDataCount,
            'ViewChartJson' => json_encode($tDef_ViewChartJson),
        ]);
        //基础变量
        View::assign([
            'AdminData'  => request()->middleware('NowAdminData'),
            'ViewTitle'  => '总览'
        ]);

        //输出模板
        return View::fetch($this->attrGReqView);
    }
}
