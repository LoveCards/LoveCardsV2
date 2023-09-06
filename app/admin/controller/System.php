<?php

namespace app\admin\controller;

use think\Request as TypeRequest;
use think\facade\View;
use think\facade\Db;
use think\facade\Config;

use app\common\Common;
use app\common\File;
use app\common\FrontEnd;
use app\common\Theme;

use app\admin\BaseController;

class System extends BaseController
{

    //中间件
    protected $middleware = [\app\admin\middleware\AdminPowerCheck::class];

    //Index
    public function Index(TypeRequest $tDef_Request)
    {
        //取系统数据
        $systemData = array_column(Db::table('system')->select()->toArray(), 'value', 'name');
        View::assign($systemData);

        //基础变量
        View::assign([
            'AdminData'  => $tDef_Request->attrLDefNowAdminAllData,
            'ViewTitle'  => '系统设置'
        ]);

        //输出模板
        return View::fetch('/system');
    }

    //View
    public function View(TypeRequest $tDef_Request)
    {
        //取系统数据
        $systemData = array_column(Db::table('system')->select()->toArray(), 'value', 'name');
        View::assign($systemData);

        //取模板config数据
        $templateDirectory = File::get_dirs('./view/index')['dir'];
        $testTemplateConfig = array();
        for ($i = 2; $i < count($templateDirectory); $i++) {
            if ($templateDirectory[$i] != '.' && $templateDirectory[$i] != '..') {
                $t = './view/index/' . $templateDirectory[$i];
                if (File::get_size($t) != 0) {
                    $testTemplateConfig[$templateDirectory[$i]] = json_decode(File::read_file($t . '/config.ini'), true);
                    $testTemplateConfig[$templateDirectory[$i]]['DirectoryName'] = $templateDirectory[$i];
                }
            }
        }
        $templateConfig = array();
        foreach ($testTemplateConfig as $value) {
            array_push($templateConfig, $value);
        }

        $nowTemplateDirectory = Config::get('lovecards.template_directory', 'index') ?: 'index';
        $nowTemplateConfig = json_decode(File::read_file('./view/index/' . $nowTemplateDirectory . '/config.ini'), true);
        $nowTemplateConfig['Config'] = Theme::mResultGetThemeConfig($nowTemplateDirectory);
        if (!$nowTemplateConfig) {
            $nowTemplateConfig = json_decode(File::read_file('./view/index/index/config.ini'), true);
        }

        //基础变量
        View::assign([
            'AdminData'  => $tDef_Request->attrLDefNowAdminAllData,
            'ViewTitle'  => '外观设置',
            //模板配置列表
            'templateConfig' => $templateConfig,
            //当前模板配置
            'nowTemplateConfig' => $nowTemplateConfig
        ]);

        //输出模板
        return View::fetch('/system-view');
    }

    //View-Set
    public function ViewSet(TypeRequest $tDef_Request)
    {
        //取系统数据
        $systemData = array_column(Db::table('system')->select()->toArray(), 'value', 'name');
        View::assign($systemData);

        $TemplateConfig = Theme::mResultGetThemeConfig(Config::get('lovecards.template_directory', 'index') ?: 'index', true);
        if (!$TemplateConfig) {
            return FrontEnd::jumpUrl('/admin/system/view', '当前主题没有配置项');
        }

        //基础变量
        View::assign([
            'AdminData'  => $tDef_Request->attrLDefNowAdminAllData,
            'ViewTitle'  => '主题设置',
            //当前模板配置
            'TemplateConfig' => $TemplateConfig
        ]);

        //输出模板
        return View::fetch('/system-view-set');
    }
}
