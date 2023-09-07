<?php

namespace app\common;

use think\Facade;
use think\facade\Config;
use think\facade\View;

class Theme extends Facade
{
    /**
     * @description: 获取模板目录
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2023-07-18 15:17:52
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     */
    protected static function mArrayGetThemeDirectory()
    {
        $d = Config::get('lovecards.template_directory', 'index') ?: 'index';
        $r = Is_dir('./view/index/' . $d);
        //dd($r);
        if ($r) {
            //当目录存在时
            $r = $d;
        } else {
            $r = 'index';
        }
        //dd($r, $d);
        return [$r, $d];
    }

    /**
     * @description: 编辑配置文件
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2023-07-18 15:16:37
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     * @param {*} $fileName
     * @param {*} $data
     */
    protected static function mBoolCoverThemeConfig($fileName, $data, $free = false, $env = 'ThemeConfig')
    {
        $fileName = '../public/view' . $fileName . '.php';
        $str_file = file_get_contents($fileName);

        if ($free == true) {
            foreach ($data as $key => $value) {
                //构建正则匹配
                $pattern = "/env\('" . $env . "\." . $key . "',\s*([^']*)\)/";
                //判断是否成功匹配
                if (preg_match($pattern, $str_file)) {
                    //匹配成功更新
                    $str_file = preg_replace($pattern, "env('" . $env . "." . $key . "', " . $value . ")", $str_file);
                }
            }
        } else {
            foreach ($data as $key => $value) {
                //构建正则匹配
                $pattern = "/env\('" . $env . "\." . $key . "',\s*'([^']*)'\)/";
                //判断是否成功匹配
                if (preg_match($pattern, $str_file)) {
                    //匹配成功更新
                    $str_file = preg_replace($pattern, "env('" . $env . "." . $key . "', '" . $value . "')", $str_file);
                }
            }
        }

        //写入并返回结果
        if (!file_put_contents($fileName, $str_file)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @description: 获取主题配置
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2023-09-07 12:57:15
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     * @param {*} $TemplateDirectory
     * @param {*} $Original
     */
    protected static function mResultGetThemeConfig($TemplateDirectory, $Original = false)
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/view/index/' . $TemplateDirectory . '/config.php';
        if (is_file($path)) {
            include $path;
            $require = array();
            if ($Original) {
                $require = $Config;
            } else {
                //选择格式数据获取
                foreach ($Config['Select'] as $key => $value) {
                    $require[$key] = $value['Element'][$value['Default']];
                }
            }
            return $require;
        } else {
            return false;
        }
    }

    /**
     * @description: 规范化View::fetch
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2023-09-07 13:06:14
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     * @param {*} $tDef_Path
     */    
    protected static function mObjectEasyViewFetch($tDef_Path)
    {
        try {
            $tDef_Result = View::fetch($tDef_Path);
        } catch (\Throwable $th) {
            return redirect('/index/404');
        }
        return $tDef_Result;
    }
}
