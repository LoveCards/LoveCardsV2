<?php

namespace app\api\controller;

use yunarch\app\api\validate\RuleUtils;

class Base
{

    var $JWT_SESSION = false;

    function __construct()
    {
        new RuleUtils(); // 确保加载通用验证类

        $this->JWT_SESSION = request()->JwtData; //JWT SESSION
    }
}
