<?php
namespace yunarch\app\api\facade;

use think\Facade;

class ValidateUtils extends Facade
{
    protected static function getFacadeClass()
    {
    	return 'yunarch\app\api\validate\Utils';
    }
}