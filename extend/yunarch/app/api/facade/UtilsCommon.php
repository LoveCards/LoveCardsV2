<?php
namespace yunarch\app\api\facade;

use think\Facade;

class UtilsCommon extends Facade
{
    protected static function getFacadeClass()
    {
    	return 'yunarch\app\api\utils\Common';
    }
}