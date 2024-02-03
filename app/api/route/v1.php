<?php

use think\facade\Route;
use think\facade\Request;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\JwtAuthLogout;

use app\api\middleware\AdminPowerCheck;
use app\api\middleware\AdminAuthCheck;

use app\api\middleware\SessionDebounce;
use app\api\middleware\GeetestCheck;

Route::get('theme/config', 'theme/Config');

Route::post('user/auth/login', 'user.Auth/Login');
Route::post('user/auth/logout', 'user.Auth/Logout');
Route::post('user/auth/register', 'user.Auth/Register');
Route::post('user/auth/captcha', 'user.Auth/Captcha');

Route::post('auth/logout', 'Auth/logout')->middleware(JwtAuthLogout::class);

//用户登入鉴权-支持游客
Route::group('', function () {
    Route::post('cards/add', 'cards/Add');
    Route::post('comments/add', 'comments/Add');
    Route::post('cards/good', 'cards/Good');
})->middleware([JwtAuthCheck::class]);

//用户登入鉴权
Route::group('', function () {
    Route::post('upload/user-images', 'upload/UserImages');
    Route::post('user/password', 'user.info/PostPassword');
    Route::post('user/email', 'user.info/PostEmail');
    Route::post('user/email-captcha', 'user.info/PostBindEmailCaptcha');

    Route::patch('user', 'user.info/Patch');
    Route::get('user', 'user.info/Get');
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

    Route::get('system/email', 'System/GetEmail');
    Route::rule('system/email', 'System/Email','PUT|PATCH');

    Route::get('system/other', 'System/GetOther');
    Route::rule('system/other', 'System/Other','PUT|PATCH');

    Route::post('system/template', 'System/template');
    Route::post('system/templateset', 'System/TemplateSet');
    Route::post('system/geetest', 'System/Geetest');
})->middleware([JwtAuthCheck::class, AdminPowerCheck::class]);

//防抖+极验鉴权(内置开关)
Route::group('', function () {
    Route::post('cards/add', 'Cards/Add');
    Route::post('comments/add', 'Comments/Add');
})->middleware([SessionDebounce::class, GeetestCheck::class]);
