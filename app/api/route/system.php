<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::group('all/system', function () {
    Route::get('update', 'System.System/update')->name('system.update')->setOption('meta', ['name' => '系统更新', 'group' => '系统']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

