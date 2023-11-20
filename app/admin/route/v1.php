<?php

use think\facade\Route;
use think\facade\Request;

use app\admin\middleware\JwtAuthCheck;

use app\admin\middleware\AdminPowerCheck;
use app\admin\middleware\AdminAuthCheck;

function fObjectEasyBindIndex($rule, $route)
{
    Route::get($rule, $route);
    Route::get($rule . '/index', $route);
}

Route::get('login', 'Auth/login');

//管理鉴权
Route::group('', function () {
    fObjectEasyBindIndex('index', 'Index/index');
    fObjectEasyBindIndex('updata', 'Updata/index');
})->middleware([AdminAuthCheck::class]);

//超管鉴权
Route::group('', function () {
})->middleware([JwtAuthCheck::class, AdminPowerCheck::class]);
