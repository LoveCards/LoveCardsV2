<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::get('dashboard', 'System.Dashboard/index')
    ->name('dashboard.index')
    ->setOption('meta', ['name' => '控制台', 'group' => '系统', 'caps' => ['dashboard.read']])
    ->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
