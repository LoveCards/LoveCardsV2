<?php

namespace app\api\controller;

//thinkphp
use think\facade\Request;
use think\exception\ValidateException;

//旧的
use app\common\Export;

//yunarch
use yunarch\app\api\validate\RuleUtils;
use yunarch\app\api\validate\Common as ApiCommonValidate;

class Base
{
    //基础参数
    var $SESSION = false;
    var $JWT_SESSION = false;

    function __construct()
    {
        new RuleUtils(); // 确保加载通用验证类

        $this->JWT_SESSION = request()->JwtData; //JWT SESSION

        $this->SESSION = [
            'data' => date('Y-m-d H:i:s'),
            'ip' => $this->getIP()
        ];
    }

    /**
     * 通用获取验证并过滤
     *
     * @param string 对应的验证类
     * @param array 对应的验证场景
     * @return array|object
     */
    public function getParams($ValidateClass, $scene)
    {
        // 获取参数并按照规则过滤
        $result = ApiCommonValidate::sceneFilter(Request::param(), $scene);

        //验证参数
        try {
            //场景参数验证
            $params = ApiCommonValidate::sceneMessage($result, $ValidateClass);
            //参数验证
            validate($ValidateClass)
                ->batch(true)
                ->check($params);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $error = $e->getError();
            return Export::Create($error, 400, '参数错误');
        }

        return $params;
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
