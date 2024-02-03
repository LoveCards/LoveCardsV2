<?php

namespace app\index\method;

use app\common\File;
use think\facade\Request;

trait Base
{
    /**
     * 从访问URL中提取出可能的AppPath
     *
     * @param string $tDef_AppPath 主题的APP路径
     * @return array 返回所有可能路径，从最短到最长无序排列
     */
    public static function mArrayEasyGetUrlAppPath($tDef_AppPath): array
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
        $lDef_TrimmedPartsEndKey = 0;
        foreach ($lDef_Parts as $tDef_Key => $tDef_Part) {
            //略过0位置
            if ($tDef_Key) {
                //路径仅支持小写匹配
                $lDef_PartialUrl .= '/' . strtolower($tDef_Part);
            }
            //echo $lDef_PartialUrl . '   ';
            //判断是否为目录
            if (substr($lDef_PartialUrl, -1) === '/') {
                // 若为目录直接验证index文件路径是否存在
                if (File::read_file($_SERVER['DOCUMENT_ROOT'] . $tDef_AppPath . $lDef_PartialUrl . 'index.html')) {
                    $tDef_NewPartialUrl = $lDef_PartialUrl . 'index';
                    //dd($lDef_TrimmedParts[$lDef_TrimmedPartsEndKey - 1]);
                    //防止与上层自动绑定Index验证通过路径重复
                    if (!isset($lDef_TrimmedParts[$lDef_TrimmedPartsEndKey - 1]) || $lDef_TrimmedParts[$lDef_TrimmedPartsEndKey - 1] != $tDef_NewPartialUrl) {
                        $lDef_TrimmedParts[] = $tDef_NewPartialUrl;
                        $lDef_TrimmedPartsEndKey++;
                    }
                };
            } else {
                // 验证html文件路径是否存在
                if (File::read_file($_SERVER['DOCUMENT_ROOT'] . $tDef_AppPath . $lDef_PartialUrl . '.html')) {
                    //防止与上层自动绑定Index验证通过路径重复
                    if (!isset($lDef_TrimmedParts[$lDef_TrimmedPartsEndKey - 1]) || $lDef_TrimmedParts[$lDef_TrimmedPartsEndKey - 1] != $lDef_PartialUrl) {
                        $lDef_TrimmedParts[] = $lDef_PartialUrl;
                        $lDef_TrimmedPartsEndKey++;
                    }
                } else {
                    //当匹配失败将尝试绑定Index再次验证文件
                    if ($lDef_PartialUrl != '/' && File::read_file($_SERVER['DOCUMENT_ROOT'] . $tDef_AppPath . $lDef_PartialUrl . '/index.html')) {
                        $tDef_NewPartialUrl = $lDef_PartialUrl . '/index';
                        //防止与上层自动绑定Index验证通过路径重复
                        if (!isset($lDef_TrimmedParts[$lDef_TrimmedPartsEndKey - 1]) || $lDef_TrimmedParts[$lDef_TrimmedPartsEndKey - 1] != $tDef_NewPartialUrl) {
                            $lDef_TrimmedParts[] = $tDef_NewPartialUrl;
                            $lDef_TrimmedPartsEndKey++;
                        }
                    }
                };
            }
        }
        //当都不存在时尝试绑定Index/index
        if (count($lDef_TrimmedParts) == 0 && File::read_file($_SERVER['DOCUMENT_ROOT'] . $tDef_AppPath .  '/index/index.html')) {
            $lDef_TrimmedParts[] = '/index/index';
        }

        return $lDef_TrimmedParts;
    }

    /**
     * 从配置数组中匹配方法与鉴权
     * @param string $tDef_AppPath
     * @param array $lDef_ThemeConfig
     * @return array ['PageAuth', 'PageAssignData']
     */
    public static function mArrayMatchThemeAppConfig($tDef_AppPath, $lDef_ThemeConfig): array
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
            if (!array_key_exists('PageAuth', $lDef_ThemeConfig)) {
                $lDef_ThemeConfig['PageAuth'] = '';
            }
            if (!array_key_exists('PageAssignData', $lDef_ThemeConfig)) {
                $lDef_ThemeConfig['PageAssignData'] = '';
            }
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
}
