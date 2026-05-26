<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;
use app\api\middleware\SessionDebounce;
use app\api\middleware\GeetestCheck;

Route::post('session/login', 'User.Session/login')->name('session.login')->setOption('meta', ['name' => '登录', 'group' => '认证', 'public' => true]);
Route::post('session/register', 'User.Session/register')
    ->name('session.register')
    ->setOption('meta', ['name' => '注册', 'group' => '认证', 'public' => true])
    ->middleware(SessionDebounce::class)->middleware(GeetestCheck::class);
Route::post('session/guest', 'User.Session/guest')->name('session.guest')->setOption('meta', ['name' => '访客登录', 'group' => '认证', 'public' => true]);
Route::post('session/captcha', 'User.Session/captcha')
    ->name('session.captcha')
    ->setOption('meta', ['name' => '获取验证码', 'group' => '认证', 'public' => true])
    ->middleware(SessionDebounce::class);

Route::group('session', function () {
    Route::post('logout', 'User.Session/logout')->name('session.logout')->setOption('meta', ['name' => '登出', 'group' => '认证']);
    Route::get('check', 'User.Session/check')->name('session.check')->setOption('meta', ['name' => 'Token校验', 'group' => '认证']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
