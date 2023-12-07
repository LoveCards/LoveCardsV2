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
        $tDef_ViewControllerPath = dirname(dirname(dirname(dirname(__FILE__)))) . '/public/theme/' . $this->attrGReqView['Theme']['DirectoryName'] . '/app/' . $tDef_ControllerName . '/' . $tDef_ActionName;
        //不存在跳转404
        if (!File::read_file($tDef_ViewControllerPath . '.html')) {
            return redirect('/index/404');
        };
        //覆盖
        if (File::read_file($tDef_ViewControllerPath . '.js')) {
            $lDef_AssignData['ViewActionJS'] = true;
        };

        //加载主题变量
        function test($tDef_ControllerName, $tDef_ActionName, $lDef_ThemeConfig)
        {
            $lDef_ResultArray = [];
            function matchArray($array, $key)
            {
                $key = lcfirst($key);
                if ($array && isset($array[$key])) {
                    return $array[$key];
                } else {
                    return false;
                }
            }

            if ($lDef_ThemeConfig) {
                //匹配目录
                $lDef_ResultArray['PageAuth'] = matchArray($lDef_ThemeConfig['PageAuth'], $tDef_ControllerName);
                //匹配文件
                $lDef_Result = matchArray($lDef_ThemeConfig['PageAuth'], $tDef_ControllerName . '/' . $tDef_ActionName);
                $lDef_Result ?
                    $lDef_ResultArray['PageAuth'] = $lDef_Result :
                    0;

                //匹配目录
                $lDef_ResultArray['PageAssignData'] = matchArray($lDef_ThemeConfig['PageAssignData'], $tDef_ControllerName);
                //匹配文件
                $lDef_Result = matchArray($lDef_ThemeConfig['PageAssignData'], $tDef_ControllerName . '/' . $tDef_ActionName);
                $lDef_Result ?
                    $lDef_ResultArray['PageAssignData'] = $lDef_Result :
                    0;
            }
            return $lDef_ResultArray;
        }
        $ds2 = test($tDef_ControllerName, $tDef_ActionName, $this->attrGReqView['Theme']['Config']);
        //dd($ds2);
        if ($ds2['PageAssignData']) {
            foreach ($ds2['PageAssignData'] as $key => $value) {
                $test = new Cards;
                $test->$value();
            }
        }

        // dd(View::engine());

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
