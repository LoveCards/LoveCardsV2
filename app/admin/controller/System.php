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
    //Index
    public function Index()
    {
        //取系统数据
        $systemData = array_column(Db::table('system')->select()->toArray(), 'value', 'name');
        View::assign($systemData);

        //基础变量
        View::assign([
            'AdminData'  => request()->middleware('NowAdminData'),
            'ViewTitle'  => '系统设置'
        ]);

        //输出模板
        return View::fetch($this->attrGReqView);
    }

    //View
    public function View()
    {
        //取系统数据
        $systemData = array_column(Db::table('system')->select()->toArray(), 'value', 'name');
        View::assign($systemData);

        //取模板config数据
        $lDef_ThemeDirectoryList = File::get_dirs('./theme')['dir'];
        sort($lDef_ThemeDirectoryList);
        $lDef_ThemeConfigList = array();
        for ($i = 2; $i < count($lDef_ThemeDirectoryList); $i++) {
            $tDef_ThemeBasePath = './theme/' . $lDef_ThemeDirectoryList[$i];
            if (File::get_size($tDef_ThemeBasePath) != 0) {
                // 以目录名为键
                // $lDef_ThemeConfigList[$lDef_ThemeDirectoryList[$i]] = json_decode(File::read_file($tDef_ThemeBasePath . '/info.ini'), true);
                // $lDef_ThemeConfigList[$lDef_ThemeDirectoryList[$i]]['DirectoryName'] = $lDef_ThemeDirectoryList[$i];
                // 无键
                $lDef_ThemeConfigList[$i - 2] = json_decode(File::read_file($tDef_ThemeBasePath . '/info.ini'), true);
                $lDef_ThemeConfigList[$i - 2]['DirectoryName'] = $lDef_ThemeDirectoryList[$i];
            }
        }

        $tDef_NowThemeDirectory = Theme::mArrayGetThemeDirectory()['N'];
        $lDef_NowThemeInfo = json_decode(File::read_file('./theme/' . $tDef_NowThemeDirectory . '/info.ini'), true);
        $lDef_NowThemeInfo['Config'] = Theme::mResultGetThemeConfig($tDef_NowThemeDirectory); //用来给前端判断主题是否可以配置

        if (!$lDef_NowThemeInfo) {
            $lDef_NowThemeInfo = json_decode(File::read_file('./theme/index/info.ini'), true);
        }

        //基础变量
        View::assign([
            'AdminData'  => request()->middleware('NowAdminData'),
            'ViewTitle'  => '外观设置',
            //模板配置列表
            'ThemeConfig' =>  $lDef_ThemeConfigList,
            //当前模板配置
            'nowThemeInfo' => $lDef_NowThemeInfo
        ]);

        //输出模板
        return View::fetch($this->attrGReqView);
    }

    //View-Set
    public function ViewSet()
    {
        //取系统数据
        $systemData = array_column(Db::table('system')->select()->toArray(), 'value', 'name');
        View::assign($systemData);

        $tDef_NowThemeConfig = Theme::mResultGetThemeConfig(Theme::mArrayGetThemeDirectory()['N'], true);
        if (!$tDef_NowThemeConfig) {
            return FrontEnd::jumpUrl('/admin/system/view', '当前主题没有配置项');
        }

        //解码输出
        if (!empty($tDef_NowThemeConfig['Text'])) {
            foreach ($tDef_NowThemeConfig['Text'] as $key => $value) {
                $tDef_NowThemeConfig['Text'][$key]['Default'] = urldecode($value['Default']);
            }
        }

        //基础变量
        View::assign([
            'AdminData'  => request()->middleware('NowAdminData'),
            'ViewTitle'  => '主题设置',
            //当前模板配置
            'TemplateConfig' => $tDef_NowThemeConfig
        ]);

        //输出模板
        return View::fetch($this->attrGReqView);
    }
}
