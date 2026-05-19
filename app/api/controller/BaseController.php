<?php

namespace app\api\controller;

//yunarch
use yunarch\utils\src\ValidateRuleExtend; // 通用验证规则

class BaseController
{
    function __construct()
    {
        $ValidateRuleExtend = new ValidateRuleExtend;
        $ValidateRuleExtend->maker(); // 加载验证规则到全局
    }
}
