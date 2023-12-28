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

//用户登入鉴权
Route::group('', function () {
    Route::post('upload/user-images', 'upload/UserImages');
    Route::post('cards/add', 'cards/Add');
})->middleware([JwtAuthCheck::class]);

//登入鉴权
Route::group('', function () {
    Route::post('admin/add', 'Admin/Add');
    Route::post('admin/edit', 'Admin/Edit');
    Route::post('admin/delete', 'Admin/Delete');

    Route::post('cards/edit', 'Cards/Edit');
    Route::post('cards/delete', 'Cards/Delete');

    Route::post('comments/edit', 'Comments/Edit');
    Route::post('comments/delete', 'Comments/Delete');

    Route::post('tags/add', 'Tags/Add');
    Route::post('tags/edit', 'Tags/Edit');
    Route::post('tags/delete', 'Tags/Delete');

    Route::get('users/index', 'Users/Index');
    Route::patch('users/patch', 'Users/Patch');
    Route::delete('users/delete', 'Users/Delete');

    Route::post('upload/user-images', 'upload/UserImages');
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

//防抖+极验鉴权(内置开关)
Route::group('', function () {
    Route::post('cards/add', 'Cards/Add');
    Route::post('comments/add', 'Comments/Add');
})->middleware([SessionDebounce::class, GeetestCheck::class]);
