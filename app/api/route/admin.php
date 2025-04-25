<?php

use think\facade\Route;
use think\facade\Request;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\JwtAuthLogout;

use app\api\middleware\SessionDebounce;
use app\api\middleware\GeetestCheck;

use yunarch\app\roles\middleware\RolesCheck;

Route::group('', function () {
    //超管
    Route::post('system/site', 'admin.System/Site');

    Route::get('system/email', 'admin.System/GetEmail');
    Route::rule('system/email', 'admin.System/Email', 'PUT|PATCH');

    Route::get('system/other', 'admin.System/GetOther');
    Route::rule('system/other', 'admin.System/Other', 'PUT|PATCH');

    Route::post('system/template', 'admin.System/template');
    Route::post('system/templateset', 'admin.System/TemplateSet');
    Route::post('system/geetest', 'admin.System/Geetest');

    Route::post('cards/setting', 'admin.Cards/Setting');

    //管理员
    Route::post('cards/edit', 'admin.Cards/Edit');
    Route::post('cards/delete', 'admin.Cards/Delete');

    Route::get('users/index', 'admin.Users/Index');
    Route::patch('users/patch', 'admin.Users/Patch');
    Route::delete('users/delete', 'admin.Users/Delete');

    Route::post('tags/add', 'admin.Tags/Add');
    Route::post('tags/edit', 'admin.Tags/Edit');
    Route::post('tags/delete', 'admin.Tags/Delete');

    Route::post('comments/edit', 'admin.Comments/Edit');
    Route::post('comments/delete', 'admin.Comments/Delete');

    //控制台
    Route::get('dashboard', 'admin.Dashboard/Index');
})->middleware([JwtAuthCheck::class, RolesCheck::class]);
