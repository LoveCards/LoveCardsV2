<?php

namespace app\api\controller;

use yunarch\app\api\validate\RuleUtils;

class Base
{
    function __construct()
    {
        new RuleUtils(); // 确保加载通用验证类
    }
}
