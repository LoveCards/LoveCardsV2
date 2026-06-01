<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::group('system', function () {
    Route::get('update', 'System.System/update')
        ->name('system.update')
        ->setOption('meta', ['name' => '系统更新', 'group' => '系统', 'caps' => ['system.update']]);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
