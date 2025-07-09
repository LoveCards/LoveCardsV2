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

    var $JWT_SESSION = false;

    function __construct()
    {
        new RuleUtils(); // 确保加载通用验证类

        $this->JWT_SESSION = request()->JwtData; //JWT SESSION
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
            $params = ApiCommonValidate::sceneMessage($result);
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
}
