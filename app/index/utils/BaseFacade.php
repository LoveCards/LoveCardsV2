<?php

namespace app\index\utils;

use think\Facade;

//Base门面
class BaseFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\index\utils\Base';
    }
}
