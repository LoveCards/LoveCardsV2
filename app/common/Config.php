<?php

namespace app\common;

use think\Facade;
use think\facade\Config as ThinkConfig;

class Config extends Facade
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
    private function mArrayGetMasterConfig()
    {
        return ThinkConfig::get($this->attrTConfigFileName);
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
}
