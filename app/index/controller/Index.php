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
        //模拟
        Request::param('Controller') ? $tDef_ControllerName = Request::param('Controller') : $tDef_ControllerName = 'index';
        Request::param('Action') ? $tDef_ActionName = Request::param('Action') : $tDef_ActionName = 'index';

        /**
         * 从访问URL中提取出可能的AppPath
         *
         * @param string $tDef_AppPath 主题的APP路径
         * @return array
         */
        function getUrlAppPath($tDef_AppPath): array
        {
            // 修剪根路径
            $lDef_Url = preg_replace('#' . Request::rootUrl() . '#', '', Request::baseUrl(), 1);
            $lDef_Parts = explode('/', $lDef_Url);

            // 预估路径
            $lDef_TrimmedParts = [];
            $lDef_PartialUrl = '';
            foreach ($lDef_Parts as $key => $part) {
                //略过0位置
                if ($key) {
                    $lDef_PartialUrl .= '/' . $part;
                }
                //echo $lDef_PartialUrl . '   ';
                // 验证html文件路径是否存在
                if (File::read_file($_SERVER['DOCUMENT_ROOT'] . $tDef_AppPath . $lDef_PartialUrl . '.html')) {
                    //防止与上层自动绑定Index验证通过路径重复
                    if (isset($array[$key - 1]) && $lDef_TrimmedParts[$key - 1] != $lDef_PartialUrl) {
                        $lDef_TrimmedParts[] = $lDef_PartialUrl;
                    }
                } else {
                    //当匹配失败将尝试绑定Index再次验证文件
                    if ($lDef_PartialUrl != '/' && File::read_file($_SERVER['DOCUMENT_ROOT'] . $tDef_AppPath . $lDef_PartialUrl . '/index' . '.html')) {
                        $lDef_TrimmedParts[] = $lDef_PartialUrl . '/index';
                    }
                };
            }

            //当都不存在时尝试绑定Index/index
            if (count($lDef_TrimmedParts) == 0 && File::read_file($_SERVER['DOCUMENT_ROOT'] . $tDef_AppPath .  '/index/index' . '.html')) {
                $lDef_TrimmedParts[] = '/index/index';
            }

            return $lDef_TrimmedParts;
        }

        // 页面路径
        $tDef_AppPathArray = getUrlAppPath('/theme/' . $this->attrGReqView['Theme']['DirectoryName'] . '/app');

        // 获取主题匹配JS路径
        $lDef_PageJsPath = $_SERVER['DOCUMENT_ROOT'] . '/theme/' . $this->attrGReqView['Theme']['DirectoryName'] . '/app' . end($tDef_AppPathArray);
        if (File::read_file($lDef_PageJsPath . '.js', true)) {
            $this->attrGReqAssignArray['ViewActionJS'] = '/app/' . strtolower($lDef_PageJsPath);
        };

        //dd(end($tDef_AppPathArray));

        //加载主题指定方法 变量
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

        //基础变量
        $this->attrGReqAssignArray['ViewTitle']  = '自定义页面';
        View::assign($this->attrGReqAssignArray);

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
