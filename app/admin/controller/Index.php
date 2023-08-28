<?php

namespace app\admin\controller;

use think\Request as TypeRequest;
use think\facade\View;
use think\facade\Db;

use app\common\Common;

class Index
{
    //中间件
    protected $middleware = [\app\admin\middleware\AdminAuthCheck::class];

    //Index
    public function Index(TypeRequest $var_t_def_request)
    {
        //函数-取图表数据
        function ChartData($key)
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
        $dataNum = [
            'cards' => Db::table('cards')->count(),
            'cardsComments' => Db::table('cards_comments')->count(),
            'good' => Db::table('good')->count()
        ];
        //取图表数据
        $dataChart = [ChartData('cards'), ChartData('cards_comments'), ChartData('good')];
        View::assign([
            'dataNum' => $dataNum,
            'dataChart' => json_encode($dataChart),
        ]);

        //基础变量
        View::assign([
            'adminData'  => $var_t_def_request->attrLDefNowAdminAllData,
            'systemVer' => Common::systemVer(),
            'systemData' => Common::systemData(),
            'viewTitle'  => '总览'
        ]);

        //输出模板
        return View::fetch('/index');
    }
}
