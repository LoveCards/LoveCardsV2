<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::get('all/dashboard', 'Dashboard/index')->name('dashboard.index')->setOption('meta', ['name' => '控制台', 'group' => '系统'])->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
