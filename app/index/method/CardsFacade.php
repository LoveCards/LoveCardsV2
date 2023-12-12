<?php

namespace app\index\method;

use think\Facade;

//Cards门面
class CardsFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\index\method\Cards';
    }
}
