<?php

use think\facade\Route;
use think\facade\Request;

use app\admin\middleware\JwtAuthCheck;

use app\admin\middleware\AdminPowerCheck;
use app\admin\middleware\AdminAuthCheck;

function fObjectEasyBindIndex($rule, $route)
{
    Route::get($rule . '$', $route);
    Route::get($rule . '/index', $route);
}

Route::get('login', 'Auth/login');

//管理鉴权
Route::group('', function () {
    Route::get('$', 'Index/Index');
    fObjectEasyBindIndex('index', 'Index/Index');

    fObjectEasyBindIndex('cards', 'Cards/Index');
    Route::group('cards', function () {
        Route::get('edit', 'Cards/Edit');
    });

    fObjectEasyBindIndex('users', 'Users/Index');

    fObjectEasyBindIndex('tags', 'Tags/Index');

    fObjectEasyBindIndex('comments', 'Comments/Index');
})->middleware([AdminAuthCheck::class]);

//超管鉴权
Route::group('', function () {
    Route::group('cards', function () {
        Route::get('setting', 'Cards/Setting');
    });

    fObjectEasyBindIndex('admin', 'Admin/Index');

    fObjectEasyBindIndex('system', 'System/Index');
    Route::group('system', function () {
        Route::get('view', 'System/View');
        Route::get('viewset', 'System/ViewSet');
    });

    fObjectEasyBindIndex('updata', 'Updata/Index');
})->middleware([AdminPowerCheck::class]);
