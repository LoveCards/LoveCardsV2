<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::group('all/captcha', function () {
    Route::get('types',              'Captcha.Captcha/types')   ->name('captcha.types')  ->setOption('meta', ['name' => '验证驱动列表',   'group' => '验证']);
    Route::get('drivers',            'Captcha.Captcha/drivers') ->name('captcha.drivers')->setOption('meta', ['name' => '验证驱动详情',   'group' => '验证']);
    Route::get(':slug/meta',         'Captcha.Captcha/meta')    ->name('captcha.meta')   ->setOption('meta', ['name' => '驱动配置信息',   'group' => '验证']);
    Route::post('install',           'Captcha.Captcha/install') ->name('captcha.install')->setOption('meta', ['name' => '安装验证驱动',   'group' => '验证']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

Route::get('captcha/config', 'Captcha.Captcha/config')
    ->name('captcha.config')
    ->setOption('meta', ['name' => '验证配置', 'group' => '验证', 'public' => true]);
