<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;

use yunarch\app\roles\middleware\RolesCheck;

Route::group('', function () {
    //超管
    Route::get('system/themes', 'admin.System/themes');

    Route::get('system/config', 'admin.System/config');
    Route::post('system/config', 'admin.System/setConfig');

    Route::post('system/site', 'admin.System/Site');

    //Route::get('system/email', 'admin.System/GetEmail');
    Route::rule('system/email', 'admin.System/Email', 'PUT|PATCH');

    //Route::get('system/other', 'admin.System/GetOther');
    //Route::rule('system/other', 'admin.System/Other', 'PUT|PATCH');

    Route::post('system/set-theme', 'admin.System/themeSet');
    Route::post('system/theme-config', 'admin.System/themeConfig');
    //Route::post('system/geetest', 'admin.System/Geetest');

    //Route::post('cards/setting', 'admin.Cards/Setting');

    //管理员
    Route::get('admin/card', 'admin.Cards/Get');
    Route::get('admin/cards', 'admin.Cards/Index');
    Route::patch('admin/card', 'admin.Cards/Patch');
    Route::delete('admin/cards', 'admin.Cards/Delete');
    Route::post('admin/cards/batch-operate', 'admin.Cards/BatchOperate');

    Route::get('admin/users', 'admin.Users/Index');
    Route::patch('admin/user', 'admin.Users/Patch');
    Route::delete('admin/user', 'admin.Users/Delete');
    Route::post('admin/users/batch-operate', 'admin.Users/BatchOperate');

    Route::get('admin/tags', 'admin.Tags/Index');
    Route::post('admin/tag', 'admin.Tags/Create');
    Route::patch('admin/tag', 'admin.Tags/Patch');
    Route::delete('admin/tag', 'admin.Tags/Delete');
    Route::post('admin/tags/batch-operate', 'admin.Tags/BatchOperate');

    Route::get('admin/comments', 'admin.Comments/Index');
    Route::patch('admin/comment', 'admin.Comments/Patch');
    Route::delete('admin/comment', 'admin.Comments/Delete');
    Route::post('admin/comments/batch-operate', 'admin.Comments/BatchOperate');

    //控制台
    Route::get('admin/dashboard', 'admin.Dashboard/Index');
})->middleware([JwtAuthCheck::class, RolesCheck::class]);
