<?php

namespace app\index\controller;

use think\facade\View;

use app\common\File;
use app\common\Theme;
use app\common\Common;
use app\index\BaseController;

use app\index\method\IndexFacade as IndexFacade;

class Index extends BaseController
{
    public function Customize()
    {
        //基础变量
        //$this->attrGReqAssignArray['ViewTitle']  = '自定义页面';

        // 页面路径
        $tDef_AppPathArray = IndexFacade::mArrayEasyGetUrlAppPath('/theme/' . $this->attrGReqView['Theme']['DirectoryName'] . '/app');
        $tDef_AppPath = end($tDef_AppPathArray);
        //dd($tDef_AppPathArray);
        // 获取主题匹配JS路径
        $lDef_PageJsPath = $_SERVER['DOCUMENT_ROOT'] . '/theme/' . $this->attrGReqView['Theme']['DirectoryName'] . '/app' . $tDef_AppPath;
        if (File::read_file($lDef_PageJsPath . '.js', true)) {
            $this->attrGReqAssignArray['ViewActionJS'] = '/app' . $tDef_AppPath;
        };

        //加载app指定数据方法与鉴权方法
        $tDef_AppConfigArray = IndexFacade::mArrayMatchThemeAppConfig($tDef_AppPath, $this->attrGReqView['Theme']['Config']);
        if ($tDef_AppConfigArray) {
            if ($tDef_AppConfigArray['PageAuth']) {
                foreach ($tDef_AppConfigArray['PageAuth'] as $value) {
                    $tDef_Result = IndexFacade::$value();
                    if ($tDef_Result) {
                        return $tDef_Result;
                    }
                }
            }
            if ($tDef_AppConfigArray['PageAssignData']) {
                foreach ($tDef_AppConfigArray['PageAssignData'] as $value) {
                    // $tDef_NewExample = new CardsMethod;
                    $tDef_Result = IndexFacade::$value();
                    if (is_array($tDef_Result)) {
                        //返回为标准格式
                        View::assign($tDef_Result['data']);
                    } else {
                        //返回对象直接执行
                        return $tDef_Result;
                    }
                }
            }
        }

        //分配变量
        View::assign($this->attrGReqAssignArray);
        //dd(View::engine());
        //输出模板
        return View::fetch('app' . $tDef_AppPath);
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
            View::assign('LCVersionInfo', Common::mArrayGetLCVersionInfo());
            View::config(['view_path' => './view/']);
            return View::fetch('error/404');
        } else {
            return redirect('/');
        }
    }
}
