<?php

namespace app\admin;

use think\facade\View;
use think\facade\Db;
use think\facade\Config;

use app\common\Common;
use app\common\Theme;
use app\common\File;

class BaseController extends Common
{
    //基础参数
    var $attrGReqTime;
    var $attrGReqIp;
    var $attrGReqView;

    function __construct()
    {
        //dd(request()->action());
        //安装检测
        @$file = fopen("../lock.txt", "r");
        if (!$file) {
            header("location:/system/index/install");
            exit;
        }

        $this->attrGReqTime = date('Y-m-d H:i:s');
        $this->attrGReqIp = $this->mStringGetIP();
        $this->attrGReqView = '/app/' . strtolower(request()->controller()) . '/' . strtolower(request()->action());
        
        $lDef_AssignData = [
            'LCVersionInfo' => $this->mArrayGetLCVersionInfo(), //程序版本信息
            'SystemData' => $this->mArrayGetDbSystemData(), //系统配置信息
            'SystemConfig' => config::get('lovecards'),
            'SystemControllerName' => strtolower(request()->controller()),
            'SystemActionName' => request()->action(),
            'ViewActionJS' => false
        ];

        //JS文件校验引入
        File::read_file(dirname(dirname(dirname(__FILE__))) . '/public/view/admin' . $this->attrGReqView . '.js') ?
            $lDef_AssignData['ViewActionJS'] = $this->attrGReqView . '.js' :
            0;

        //公共模板变量
        View::assign($lDef_AssignData);
    }
}
