<?php

namespace app\common;

use think\Facade;

class ConfigFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\common\Config';
    }
}
