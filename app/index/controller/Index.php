<?php

namespace app\index\controller;

//TP类
use think\facade\View;

//类
use app\common\Common;

class Index
{

    //输出
    public function index()
    {

        //基础变量
        View::assign([
            'systemVer' => Common::systemVer(),
            'systemData' => Common::systemData(),
            'viewTitle'  => '推荐',
            'viewDescription' => false,
            'viewKeywords' => false
        ]);

        //输出模板
        return View::fetch('/index-env');
    }
}
