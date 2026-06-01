<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::group('permissions', function () {
    Route::get('', 'Rbac.Permissions/list')
        ->name('permissions.list')
        ->setOption('meta', ['name' => '权限列表', 'group' => '权限', 'caps' => ['permissions.read']]);

    Route::get('all', 'Rbac.Permissions/all')
        ->name('permissions.all')
        ->setOption('meta', ['name' => '全部权限', 'group' => '权限', 'caps' => ['permissions.read']]);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
