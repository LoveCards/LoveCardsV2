<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::group('captcha', function () {
    Route::get('types', 'Captcha.Captcha/types')
        ->name('captcha.types')
        ->setOption('meta', ['name' => '验证驱动列表', 'group' => '验证', 'caps' => ['captcha.read']]);

    Route::get('drivers', 'Captcha.Captcha/drivers')
        ->name('captcha.drivers')
        ->setOption('meta', ['name' => '验证驱动详情', 'group' => '验证', 'caps' => ['captcha.read']]);

    Route::get(':slug/meta', 'Captcha.Captcha/meta')
        ->name('captcha.meta')
        ->setOption('meta', ['name' => '驱动配置信息', 'group' => '验证', 'caps' => ['captcha.read']]);

    Route::post('install', 'Captcha.Captcha/install')
        ->name('captcha.install')
        ->setOption('meta', ['name' => '安装验证驱动', 'group' => '验证', 'caps' => ['captcha.install']]);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

// 公开验证配置
Route::get('captcha/config', 'Captcha.Captcha/config')
    ->name('captcha.config')
    ->setOption('meta', ['name' => '验证配置', 'group' => '验证', 'public' => true]);
