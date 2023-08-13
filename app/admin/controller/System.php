<?php

namespace app\admin\controller;

//TP类
use think\facade\View;
use think\facade\Db;

//类
use app\common\Common;
use app\common\File;
use think\facade\Config;

class System
{

    //Index
    public function index()
    {
        //验证身份并返回数据
        $userData = Common::validateViewAuth();
        if ($userData[0] == false) {
            //跳转返回消息
            return Common::jumpUrl('/admin/login/index', '请先登入');
        }
        //验证权限
        if ($userData[1]['power'] != 0) {
            return Common::jumpUrl('/admin/index', '权限不足');
        }

        //取系统数据
        $systemData = array_column(Db::table('system')->select()->toArray(), 'value', 'name');
        View::assign($systemData);

        //基础变量
        View::assign([
            'adminData'  => $userData[1],
            'configData' => Config::get('lovecards'),
            'systemVer' => Common::systemVer(),
            'viewTitle'  => '系统设置'
        ]);

        //输出模板
        return View::fetch('/system');
    }

    //View
    public function View()
    {
        //验证身份并返回数据
        $userData = Common::validateViewAuth();
        if ($userData[0] == false) {
            //跳转返回消息
            return Common::jumpUrl('/admin/login/index', '请先登入');
        }
        //验证权限
        if ($userData[1]['power'] != 0) {
            return Common::jumpUrl('/admin/index', '权限不足');
        }

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

        $nowTemplateConfig = json_decode(File::read_file('./view/index/' . Config::get('lovecards.template_directory', 'index') . '/config.ini'), true);
        if (!$nowTemplateConfig) {
            $nowTemplateConfig = json_decode(File::read_file('./view/index/index/config.ini'), true);
        }

        //基础变量
        View::assign([
            'adminData'  => $userData[1],
            'systemVer' => Common::systemVer(),
            'viewTitle'  => '外观设置',
            //模板配置列表
            'templateConfig' => $templateConfig,
            //当前模板配置
            'nowTemplateConfig' => $nowTemplateConfig
        ]);

        //输出模板
        return View::fetch('/system-view');
    }
}
