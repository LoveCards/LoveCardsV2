<?php

namespace app\index\method;

use think\Facade;

//门面
class IndexFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\index\method\Index';
    }
}
