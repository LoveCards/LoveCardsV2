<?php
namespace yunarch\utils\api\facade;

use think\Facade;

class Api extends Facade
{
    protected static function getFacadeClass()
    {
    	return 'yunarch\utils\api\Api';
    }
}