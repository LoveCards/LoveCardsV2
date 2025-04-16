<?php
namespace yunarch\app\api\facade;

use think\Facade;

class ControllerUtils extends Facade
{
    protected static function getFacadeClass()
    {
    	return 'yunarch\app\api\controller\Utils';
    }
}