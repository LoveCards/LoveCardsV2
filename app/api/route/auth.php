<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;
use app\api\middleware\SessionDebounce;
use app\api\middleware\GeetestCheck;

// 公开路由（游客可访问）
Route::post('auth/login', 'Auth/login')->name('auth.login')->setOption('meta', ['name' => '登录', 'group' => '认证', 'public' => true]);
Route::post('auth/register', 'Auth/register')
    ->name('auth.register')
    ->setOption('meta', ['name' => '注册', 'group' => '认证', 'public' => true])
    ->middleware(SessionDebounce::class)->middleware(GeetestCheck::class);
Route::post('auth/guest', 'Auth/guest')->name('auth.guest')->setOption('meta', ['name' => '访客登录', 'group' => '认证', 'public' => true]);
Route::post('auth/captcha', 'Auth/captcha')
    ->name('auth.captcha')
    ->setOption('meta', ['name' => '获取验证码', 'group' => '认证', 'public' => true])
    ->middleware(SessionDebounce::class);

// 需鉴权
Route::group('auth', function () {
    Route::post('logout', 'Auth/logout')->name('auth.logout')->setOption('meta', ['name' => '登出', 'group' => '认证']);
    Route::get('check', 'Auth/check')->name('auth.check')->setOption('meta', ['name' => 'Token校验', 'group' => '认证']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

// 点赞列表
Route::get('likes', 'Likes/list')->name('likes.list')->setOption('meta', ['name' => '我的点赞', 'group' => '点赞'])->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
Route::delete('likes/:id', 'Likes/unlike')->name('likes.unlike')->setOption('meta', ['name' => '取消点赞', 'group' => '点赞'])->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
