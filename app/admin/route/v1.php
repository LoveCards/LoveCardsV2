<?php

use think\facade\Route;
use think\facade\Request;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\JwtAuthLogout;

use app\api\middleware\AdminPowerCheck;
use app\api\middleware\AdminAuthCheck;

use app\api\middleware\SessionDebounce;
use app\api\middleware\GeetestCheck;

Route::get('login', 'Auth/login');

//管理鉴权
Route::group('', function () {
    Route::get('index', 'Admin/index');
})->middleware([JwtAuthCheck::class, AdminAuthCheck::class]);

//超管鉴权
Route::group('', function () {
    Route::post('cards/setting', 'Cards/Setting');

    Route::post('system/site', 'System/Site');
    Route::post('system/email', 'System/Email');
    Route::post('system/template', 'System/template');
    Route::post('system/templateset', 'System/TemplateSet');
    Route::post('system/geetest', 'System/Geetest');
})->middleware([JwtAuthCheck::class, AdminPowerCheck::class]);