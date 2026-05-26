<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;
use app\api\middleware\SessionDebounce;
use app\api\middleware\GeetestCheck;

Route::post('auth/login', 'User.Auth/login')->name('auth.login')->setOption('meta', ['name' => '登录', 'group' => '认证', 'public' => true]);
Route::post('auth/register', 'User.Auth/register')
    ->name('auth.register')
    ->setOption('meta', ['name' => '注册', 'group' => '认证', 'public' => true])
    ->middleware(SessionDebounce::class)->middleware(GeetestCheck::class);
Route::post('auth/guest', 'User.Auth/guest')->name('auth.guest')->setOption('meta', ['name' => '访客登录', 'group' => '认证', 'public' => true]);
Route::post('auth/captcha', 'User.Auth/captcha')
    ->name('auth.captcha')
    ->setOption('meta', ['name' => '获取验证码', 'group' => '认证', 'public' => true])
    ->middleware(SessionDebounce::class);

Route::group('auth', function () {
    Route::post('logout', 'User.Auth/logout')->name('auth.logout')->setOption('meta', ['name' => '登出', 'group' => '认证']);
    Route::get('check', 'User.Auth/check')->name('auth.check')->setOption('meta', ['name' => 'Token校验', 'group' => '认证']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
