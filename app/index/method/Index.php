<?php

namespace app\index\method;

require_once 'trait/Base.php';
require_once 'trait/Auth.php';
require_once 'trait/Users.php';
require_once 'trait/Cards.php';

//合并类
class Index
{
    use Base,Auth,Users,Cards;
}