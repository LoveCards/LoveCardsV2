<?php

namespace app\index\controller;

use think\facade\View;
use think\facade\Db;

use app\common\File;
use app\common\Common;
use app\common\Theme;
use app\index\BaseController;
use think\facade\Request;

use app\index\controller\Cards;

class Index extends BaseController
{
    public function Customize()
    {
        //基础变量
        View::assign([
            'ViewTitle'  => '自定义页面',
        ]);
        //模拟
        Request::param('Controller') ? $tDef_ControllerName = Request::param('Controller') : $tDef_ControllerName = 'index';
        Request::param('Action') ? $tDef_ActionName = Request::param('Action') : $tDef_ActionName = 'index';
        //页面路径
        $tDef_ViewControllerPath = dirname(dirname(dirname(dirname(__FILE__)))) . '/public/theme/' . $this->attrGDefNowThemeDirectoryName . '/app/' . $tDef_ControllerName . '/' . $tDef_ActionName;
        //不存在跳转404
        if (!File::read_file($tDef_ViewControllerPath . '.html')) {
            return redirect('/index/404');
        };
        //覆盖
        if (File::read_file($tDef_ViewControllerPath . '.js')) {
            $lDef_AssignData['ViewActionJS'] = true;
        };

        //加载主题变量
        $obj = new Cards();
        $obj->CardList();

        //输出模板
        return View::fetch('app' . '/' . $tDef_ControllerName . '/' . $tDef_ActionName);
    }

    public function Error()
    {
        //恢复view为系统配置
        Theme::mObjectEasySetViewConfig(0);

        $code = request()->param('code');

        //输出模板
        if ($code == 404) {
            View::assign([
                'ViewTitle'  => '页面走丢了',
                'ViewKeywords' => '',
                'ViewDescription' => ''
            ]);
            return View::fetch('error/404');
        } else {
            return redirect('/');
        }
    }
}
