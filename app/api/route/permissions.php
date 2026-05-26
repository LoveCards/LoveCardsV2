<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

// 权限管理（管理员）
Route::group('all/permissions', function () {
    Route::get('', 'Auth.Permissions/list')->name('permissions.list')->setOption('meta', ['name' => '权限列表', 'group' => '权限']);
    Route::get('all', 'Auth.Permissions/all')->name('permissions.all')->setOption('meta', ['name' => '全部权限', 'group' => '权限']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
