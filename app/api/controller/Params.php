<?php

namespace app\api\controller;

use think\facade\Request;
use think\exception\ValidateException;

use app\common\Export;

use yunarch\utils\src\ValidateExtend;
use yunarch\validate\ModelList as ModelListValidate;
use yunarch\validate\Common as CommonValidate;

class Params
{
    var $ValidateExtend;
    var $CommonValidate;
    var $ModelListValidate;

    function __construct()
    {
        $this->ModelListValidate = new ModelListValidate();
        $this->CommonValidate = new CommonValidate();

        $this->ValidateExtend = new ValidateExtend();
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
        $result = $this->ValidateExtend->sceneFilter(Request::param(), $scene);

        //验证参数
        try {
            //场景参数验证
            $params = $this->ValidateExtend->sceneMessage($result, $ValidateClass);
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
     * 通用获取验证并过滤搜索分页参数
     *
     * @return array|object
     */
    public function IndexParams()
    {
        // 获取参数并按照规则过滤
        $params = $this->ValidateExtend->sceneFilter(Request::param(), ModelListValidate::$all_scene['Defult']);
        // search_keys转数组
        $params = $this->ValidateExtend->paramsJsonToArray('search_keys', $params['pass']);

        //验证参数
        try {
            validate(ModelListValidate::class)
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
