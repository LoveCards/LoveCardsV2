<?php

use think\facade\Route;
use think\facade\Request;

use app\admin\middleware\JwtAuthCheck;

use app\admin\middleware\AdminPowerCheck;
use app\admin\middleware\AdminAuthCheck;

Route::get('login', 'Auth/login');

//管理鉴权
Route::group('', function () {
    Route::get('index', 'Index/index');
})->middleware([AdminAuthCheck::class]);

//超管鉴权
Route::group('', function () {
    Route::post('cards/setting', 'Cards/Setting');

    Route::post('system/site', 'System/Site');
    Route::post('system/email', 'System/Email');
    Route::post('system/template', 'System/template');
    Route::post('system/templateset', 'System/TemplateSet');
    Route::post('system/geetest', 'System/Geetest');
})->middleware([JwtAuthCheck::class, AdminPowerCheck::class]);