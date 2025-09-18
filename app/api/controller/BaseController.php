<?php

namespace app\api\controller;

//旧的
use app\common\ConfigFacade;

//yunarch
use yunarch\utils\src\ValidateRuleExtend; // 通用验证规则

class BaseController
{
    //基础参数
    var $SESSION;
    var $JWT_SESSION;
    var $SYSTEM_CONFIG;

    function __construct()
    {
        $ValidateRuleExtend = new ValidateRuleExtend;
        $ValidateRuleExtend->maker(); // 加载验证规则到全局

        $this->JWT_SESSION = request()->JwtData; //JWT SESSION

        $this->SESSION = [
            'date' => date('Y-m-d H:i:s'),
            'ip' => $this->getIP()
        ];

        $this->SYSTEM_CONFIG = ConfigFacade::mArrayGetMasterConfig();
    }

    /**
     * 获取IP
     *
     * @param integer $type
     * @return string
     */
    public static function getIP($type = 0): string
    {
        $type       =  $type ? 1 : 0;
        static $ip  =   NULL;
        if ($ip !== NULL) return $ip[$type];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown', $arr);
            if (false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = ip2long($ip);
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }
}
