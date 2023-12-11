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


    /**
     * 从访问URL中提取出可能的AppPath
     *
     * @param string $tDef_AppPath 主题的APP路径
     * @return array
     */
    protected function mArrayEasyGetUrlAppPath($tDef_AppPath): array
    {
        // 修剪根路径
        $lDef_Url = preg_replace('#' . Request::rootUrl() . '#', '', Request::baseUrl(), 1);
        $lDef_Parts = explode('/', $lDef_Url);
        //检查数组长度异常直接弹出
        if (count($lDef_Parts) >= 100) {
            return [];
        }

        // 预估路径
        $lDef_TrimmedParts = [];
        $lDef_PartialUrl = '';
        foreach ($lDef_Parts as $tDef_Key => $tDef_Part) {
            //略过0位置
            if ($tDef_Key) {
                $lDef_PartialUrl .= '/' . $tDef_Part;
            }
            //echo $lDef_PartialUrl . '   ';
            // 验证html文件路径是否存在
            if (File::read_file($_SERVER['DOCUMENT_ROOT'] . $tDef_AppPath . $lDef_PartialUrl . '.html')) {
                //防止与上层自动绑定Index验证通过路径重复
                if (!isset($lDef_TrimmedParts[$tDef_Key - 1]) || $lDef_TrimmedParts[$tDef_Key - 1] != $lDef_PartialUrl) {
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

    /**
     * 从配置数组中匹配方法与鉴权
     * @param string $tDef_AppPath
     * @param array $lDef_ThemeConfig
     * @return array
     */
    protected function mArrayMatchThemeAppConfig($tDef_AppPath, $lDef_ThemeConfig): array
    {
        $lDef_ResultArray = [];
        $tDef_AppPath = strtolower($tDef_AppPath);

        //匹配方法
        function matchArray($array, $key)
        {
            if ($array && isset($array[$key])) {
                return $array[$key];
            } else {
                return false;
            }
        }

        //优先匹配文件 匹配到停止
        if ($lDef_ThemeConfig) {
            //匹配文件
            $lDef_ResultArray['PageAuth'] = matchArray($lDef_ThemeConfig['PageAuth'], $tDef_AppPath);
            $lDef_ResultArray['PageAssignData'] = matchArray($lDef_ThemeConfig['PageAssignData'], $tDef_AppPath);
            //没有主机匹配目录
            if ($lDef_ResultArray['PageAuth'] == false || $lDef_ResultArray['PageAssignData'] == false) {
                $lDef_Parts = explode('/', $tDef_AppPath);
                foreach ($lDef_Parts as $value) {
                    array_pop($lDef_Parts);
                    $tDef_AppFolderPath = implode('/', $lDef_Parts) . '/';
                    if ($lDef_ResultArray['PageAuth'] == false) {
                        $lDef_ResultArray['PageAuth'] = matchArray($lDef_ThemeConfig['PageAuth'], $tDef_AppFolderPath);
                    }
                    if ($lDef_ResultArray['PageAssignData'] == false) {
                        $lDef_ResultArray['PageAssignData'] = matchArray($lDef_ThemeConfig['PageAssignData'], $tDef_AppFolderPath);
                    }
                    if ($lDef_ResultArray['PageAuth'] != false || $lDef_ResultArray['PageAssignData'] != false) {
                        break;
                    }
                }
            }
        }
        return $lDef_ResultArray;
    }

    public function Customize()
    {

        //基础变量
        //$this->attrGReqAssignArray['ViewTitle']  = '自定义页面';

        // 页面路径
        $tDef_AppPathArray = $this->mArrayEasyGetUrlAppPath('/theme/' . $this->attrGReqView['Theme']['DirectoryName'] . '/app');
        $tDef_AppPath = end($tDef_AppPathArray);
        // 获取主题匹配JS路径
        $lDef_PageJsPath = $_SERVER['DOCUMENT_ROOT'] . '/theme/' . $this->attrGReqView['Theme']['DirectoryName'] . '/app' . $tDef_AppPath;
        if (File::read_file($lDef_PageJsPath . '.js', true)) {
            $this->attrGReqAssignArray['ViewActionJS'] = '/app/' . strtolower($lDef_PageJsPath);
        };

        //dd($tDef_AppPath);
        //加载app指定方法 变量
        //dd($tDef_AppPath);
        $tDef_AppConfigArray = $this->mArrayMatchThemeAppConfig($tDef_AppPath, $this->attrGReqView['Theme']['Config']);
        //dd($tDef_);
        if ($tDef_AppConfigArray['PageAssignData']) {
            foreach ($tDef_AppConfigArray['PageAssignData'] as $key => $value) {
                $test = new Cards;
                $test->$value();
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
