<?php

use think\facade\Route;
use think\facade\Request;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\JwtAuthLogout;

use app\api\middleware\AdminPowerCheck;
use app\api\middleware\AdminAuthCheck;

use app\api\middleware\SessionDebounce;
use app\api\middleware\GeetestCheck;

Route::post('auth/logout', 'Auth/logout')->middleware(JwtAuthLogout::class);

//登入鉴权
Route::group('', function () {
    Route::post('admin/add', 'Admin/Add');
    Route::post('admin/edit', 'Admin/Edit');
    Route::post('admin/delete', 'Admin/Delete');

    Route::post('cards/add', 'Cards/Add');
    Route::post('cards/edit', 'Cards/Edit');
    Route::post('cards/delete', 'Cards/Delete');
})->middleware([JwtAuthCheck::class, AdminAuthCheck::class]);

//超管鉴权
Route::group('', function () {
    Route::post('cards/setting', 'Cards/Setting');
})->middleware([JwtAuthCheck::class, AdminPowerCheck::class]);

//防抖+极验鉴权
Route::group('', function () {
    Route::post('cards/add', 'Cards/Add');
})->middleware([SessionDebounce::class, GeetestCheck::class]);
