<?php

namespace app\common;

use think\Facade;
use think\facade\Db;

use app\common\Config as Conf;

class Base
{
    //通用代理
    public static function Conf()
    {
        return new Conf();
    }
}
