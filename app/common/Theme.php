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
        $N = Config::get('lovecards.theme_directory', 'index');
        $P = 'theme/' . $N;
        if (!Is_dir($P)) {
            $N = 'index';
            $P = 'theme/index';
        }
        return ['P' => $P, 'N' => $N];
    }

    /**
     * @description: 编辑配置文件
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2023-09-07 15:52:36
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     * @param {*} $fileName
     * @param {*} $Select
     * @param {*} $Text
     */
    protected static function mBoolCoverThemeConfig($fileName, $data)
    {
        $fileName = '../public/theme/' . $fileName . '/config.php';
        $str_file = file_get_contents($fileName);
        $env = 'ThemeConfig';

        foreach ($data as $key => $value) {
            //构建正则匹配
            $pattern = "/env\('" . $env . "\." . $key . "',\s*([^']*)\)/";
            $replacement =  "env('" . $env . "." . $key . "', " . $value . ")";

            if (substr($key, 0, 4) === "Text") {
                $pattern = "/env\('" . $env . "\." . $key . "',\s*'([^']*)'\)/";
                //单行转义处理
                $value = urlencode($value);
                $replacement =  "env('" . $env . "." . $key . "', '" . $value . "')";
            }

            //判断是否成功匹配
            if (preg_match($pattern, $str_file)) {
                $str_file = preg_replace($pattern, $replacement, $str_file);
            }
        }

        //写入并返回结果
        try {
            file_put_contents($fileName, $str_file);
            return true;
        } catch (\Throwable $th) {
            return false;
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
        $path = $_SERVER['DOCUMENT_ROOT'] . '/theme/' . $TemplateDirectory . '/config.php';
        if (is_file($path)) {
            $Config = include $path;
            $require = array();
            if ($Original) {
                $require = $Config;
            } else {
                //选择格式数据获取
                if (array_key_exists('Select', $Config)) {
                    foreach ($Config['Select'] as $key => $value) {
                        $require[$key] = $value['Element'][$value['Default']];
                    }
                }
                if (array_key_exists('Text', $Config)) {
                    foreach ($Config['Text'] as $key => $value) {
                        //反转义
                        $require[$key] = urldecode($value['Default']);
                        //dd($require[$key]);
                    }
                }
            }
            //dd($require);
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

    /**
     * @description: 根据主题设置View::config
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2023-09-09 16:12:51
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     * @param {*} $tDef_ThemeDirectoryName
     */
    protected static function mObjectEasySetViewConfig($tDef_ThemeDirectoryName = 0)
    {
        if (empty($tDef_ThemeDirectoryName)) {
            $tDef_Config = [
                'view_path' => 'view/',
                'tpl_replace_string' => Config::get('view.tpl_replace_string')
            ];
        } else {
            $tDef_Config = [
                'view_path' => 'theme/' . $tDef_ThemeDirectoryName . '/',
                'tpl_replace_string' => Config::get('view.tpl_replace_string')
            ];
            $tDef_Config['tpl_replace_string']['{__ThemeUrlPath__}'] = '/theme/' . $tDef_ThemeDirectoryName;
        }
        return View::config($tDef_Config);
    }
}
