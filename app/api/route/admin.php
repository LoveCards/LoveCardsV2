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
    Route::get('admin/card', 'admin.Cards/Get');
    Route::get('admin/cards', 'admin.Cards/Index');
    Route::patch('admin/cards', 'admin.Cards/Patch');
    Route::delete('admin/cards', 'admin.Cards/Delete');

    Route::get('admin/users', 'admin.Users/Index');
    Route::patch('admin/users', 'admin.Users/Patch');
    Route::delete('admin/users', 'admin.Users/Delete');

    Route::get('admin/tags', 'admin.Tags/Index');
    Route::post('admin/tags', 'admin.Tags/Post');
    Route::patch('admin/tags', 'admin.Tags/Patch');
    Route::delete('admin/tags', 'admin.Tags/Delete');

    Route::patch('admin/comments', 'admin.Comments/Patch');
    Route::post('admin/comments', 'admin.Comments/Delete');

    //控制台
    Route::get('admin/dashboard', 'admin.Dashboard/Index');
})->middleware([JwtAuthCheck::class, RolesCheck::class]);
