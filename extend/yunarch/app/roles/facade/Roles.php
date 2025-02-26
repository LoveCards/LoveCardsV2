<?php
namespace yunarch\app\roles\facade;

use think\Facade;

class Roles extends Facade
{
    protected static function getFacadeClass()
    {
    	return 'yunarch\app\roles\Roles';
    }
}