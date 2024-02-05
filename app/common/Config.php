<?php

namespace app\common;

use think\facade\Config as ThinkConfig;

class Config
{
    private $attrTConfigFileName;

    public $attrTMasterConfig;

    public function __construct()
    {
        //定义文件名
        $this->attrTConfigFileName = "master";

        //定义全部配置
        $this->attrTMasterConfig = $this->mArrayGetMasterConfig();
    }

    /**
     * 获取核心配置
     *
     * @return Array
     */
    public function mArrayGetMasterConfig()
    {
        return ThinkConfig::get($this->attrTConfigFileName);
    }

    /**
     * 设置核心配置
     *
     * @param array $data 要求为自动模式的Array格式
     * @return boolean
     */
    public function mBoolSetMasterConfig($data = []): bool
    {
        return $this->mBoolCoverConfig($this->attrTConfigFileName, $data, 'auto');
    }

    /**
     * 暴力搜索主配置
     *
     * @param String $lReq_Key
     * @return Array 由深到潜排列
     */
    public function mArraySearchConfigKey($lReq_Key)
    {
        return $this->mArraySearchNestedArray($lReq_Key, $this->attrTMasterConfig);
    }

    /**
     * 搜索多维数组中的键
     *
     * @param String $searchKey
     * @param Array $Array
     * @param Array $result
     * @return Array
     */
    private function mArraySearchNestedArray($searchKey, $array, $result = [])
    {
        foreach ($array as $key => $value) {
            if ($key === $searchKey) {
                $result[] = $value;
            }

            if (is_array($value)) {
                $result = array_merge($result, $this->mArraySearchNestedArray($searchKey, $value));
            }
        }

        return $result;
    }

    /**
     * 编辑配置文件
     *
     * @param string $filename
     * @param array $data
     * @param boolean|string $free bool|'auto'使用自动模式传入格式为[key=>[value,free=>bool]]
     * @param string $env
     * @return boolean
     */
    public function mBoolCoverConfig($filename = '', $data = [], $free = false, $env = ''): bool
    {
        if (!$env) {
            $env = $filename;
        }
        $filename = '../config/' . $filename . '.php';
        $str_file = file_get_contents($filename);

        foreach ($data as $key => $value) {

            $freePattern = "/env\('" . $env . "\." . $key . "',\s*([^']*)\)/";
            $pattern = "/env\('" . $env . "\." . $key . "',\s*'([^']*)'\)/";

            if ($free === true) {
                //判断是否成功匹配
                if (preg_match($freePattern, $str_file)) {
                    //匹配成功更新
                    $str_file = preg_replace($freePattern, "env('" . $env . "." . $key . "', " . $value . ")", $str_file);
                }
            } elseif ($free === 'auto') {
                //自动构建正则匹配
                if ($value['free'] == true) {
                    if (preg_match($freePattern, $str_file)) {
                        $str_file = preg_replace($freePattern, "env('" . $env . "." . $key . "', " . $value['value'] . ")", $str_file);
                    }
                } else {
                    if (preg_match($pattern, $str_file)) {
                        $str_file = preg_replace($pattern, "env('" . $env . "." . $key . "', '" . $value['value'] . "')", $str_file);
                    }
                }
            } else {
                if (preg_match($pattern, $str_file)) {
                    $str_file = preg_replace($pattern, "env('" . $env . "." . $key . "', '" . $value . "')", $str_file);
                }
            }
        }

        //写入并返回结果
        try {
            file_put_contents($filename, $str_file);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
