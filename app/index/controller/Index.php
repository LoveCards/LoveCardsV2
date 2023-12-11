<?php

namespace app\index\controller;

use think\facade\View;
use think\facade\Db;

use app\common\File;
use app\common\Common;
use app\common\Theme;
use app\index\BaseController;

use app\index\utils\BaseFacade;
use app\index\method\Cards;
use app\index\utils\Auth;

class Index extends BaseController
{
    public function Customize()
    {
        dd(Auth::JwtCheck());
        //基础变量
        //$this->attrGReqAssignArray['ViewTitle']  = '自定义页面';

        // 页面路径
        $tDef_AppPathArray = BaseFacade::mArrayEasyGetUrlAppPath('/theme/' . $this->attrGReqView['Theme']['DirectoryName'] . '/app');
        $tDef_AppPath = end($tDef_AppPathArray);
        // 获取主题匹配JS路径
        $lDef_PageJsPath = $_SERVER['DOCUMENT_ROOT'] . '/theme/' . $this->attrGReqView['Theme']['DirectoryName'] . '/app' . $tDef_AppPath;
        if (File::read_file($lDef_PageJsPath . '.js', true)) {
            $this->attrGReqAssignArray['ViewActionJS'] = '/app' . $tDef_AppPath;
        };
        //dd($tDef_AppPath);

        //加载app指定方法 变量
        $tDef_AppConfigArray = BaseFacade::mArrayMatchThemeAppConfig($tDef_AppPath, $this->attrGReqView['Theme']['Config']);
        if ($tDef_AppConfigArray['PageAssignData']) {
            foreach ($tDef_AppConfigArray['PageAssignData'] as $value) {
                $tDef_NewExample = new Cards;
                $tDef_NewExample->$value();
            }
        }
        if ($tDef_AppConfigArray['PageAuth']) {
            foreach ($tDef_AppConfigArray['PageAuth'] as $value) {
                $tDef_NewExample = new Cards;
                $tDef_NewExample->$value();
            }
        }

        //分配变量
        View::assign($this->attrGReqAssignArray);

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
            return View::fetch('error/404');
        } else {
            return redirect('/');
        }
    }
}
