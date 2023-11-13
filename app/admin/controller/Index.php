<?php

namespace app\admin\controller;

use think\Request as TypeRequest;
use think\facade\View;
use think\facade\Db;

use app\common\Common;
use app\admin\BaseController;

class Index extends BaseController
{
    //中间件
    //protected $middleware = [\app\admin\middleware\AdminAuthCheck::class];

    //Index
    public function Index(TypeRequest $tDef_Request)
    {
        //函数-取图表数据
        function fArrayGetChartData($key)
        {
            for ($i = 1; $i <= 6; $i++) {
                $time = date('Y-m-d', strtotime('-' . $i . 'day'));
                $arr[0][$i] = Db::table($key)->whereDay('time', $time)->count();
                $arr[1][$i] = $time;
                if ($i == 1) $arr[1][$i] = '昨日';
            }
            $arr[0] = Array_reverse($arr[0]);
            $arr[1] = Array_reverse($arr[1]);
            return $arr;
        }

        //取总览数据
        $tDef_ViewDataCount = [
            'cards' => Db::table('cards')->count(),
            'cardsComments' => Db::table('cards_comments')->count(),
            'good' => Db::table('good')->count()
        ];
        //取图表数据
        $tDef_ViewChartJson = [fArrayGetChartData('cards'), fArrayGetChartData('cards_comments'), fArrayGetChartData('good')];
        View::assign([
            'ViewDataCount' => $tDef_ViewDataCount,
            'ViewChartJson' => json_encode($tDef_ViewChartJson),
        ]);

        //基础变量
        View::assign([
            'AdminData'  => $tDef_Request->attrLDefNowAdminAllData,
            'ViewTitle'  => '总览'
        ]);

        //输出模板
        return View::fetch('/index');
    }
}
