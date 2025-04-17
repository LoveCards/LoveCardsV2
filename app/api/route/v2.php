<?php

use think\facade\Route;
use think\facade\Request;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\JwtAuthLogout;

use app\api\middleware\AdminPowerCheck;
use app\api\middleware\AdminAuthCheck;

use app\api\middleware\SessionDebounce;
use app\api\middleware\GeetestCheck;

use yunarch\app\roles\middleware\RolesCheck;

Route::group('', function () {
    Route::patch('user/info', 'user.info/Patch');
    Route::get('user/info', 'user.info/Get');
})->middleware([JwtAuthCheck::class, RolesCheck::class]);
