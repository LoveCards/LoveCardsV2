<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

// ─── users/me 路由（只需 token） ───
Route::group('users/me', function () {
    Route::get('', 'User.Profile/get')
        ->name('users.me.get')
        ->setOption('meta', ['name' => '我的信息', 'group' => '用户']);

    Route::patch('', 'User.Profile/update')
        ->name('users.me.update')
        ->setOption('meta', ['name' => '编辑我的信息', 'group' => '用户']);

    Route::post('password', 'User.Profile/password')
        ->name('users.me.password')
        ->setOption('meta', ['name' => '修改密码', 'group' => '用户']);

    Route::post('email', 'User.Profile/email')
        ->name('users.me.email')
        ->setOption('meta', ['name' => '绑定邮箱', 'group' => '用户']);

    Route::post('email-captcha', 'User.Profile/emailCaptcha')
        ->name('users.me.emailCaptcha')
        ->setOption('meta', ['name' => '邮箱验证码', 'group' => '用户']);
})->middleware(JwtAuthCheck::class);

// ─── 用户管理路由（合并 all/ 前缀） ───
Route::group('users', function () {
    Route::get('', 'User.Users/list')
        ->name('users.list')
        ->setOption('meta', ['name' => '用户列表', 'group' => '用户', 'caps' => ['users.read', 'users.read.all']]);

    Route::get(':id', 'User.Users/get')
        ->name('users.get')
        ->setOption('meta', ['name' => '用户详情', 'group' => '用户', 'caps' => ['users.read', 'users.read.all']]);

    Route::patch(':id', 'User.Users/update')
        ->name('users.update')
        ->setOption('meta', ['name' => '编辑用户', 'group' => '用户', 'caps' => ['users.update', 'users.update.all']]);

    Route::delete(':id', 'User.Users/delete')
        ->name('users.delete')
        ->setOption('meta', ['name' => '删除用户', 'group' => '用户', 'caps' => ['users.delete', 'users.delete.all']]);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

// ─── batch 路由（只检查 token） ───
Route::post('users/batch', 'User.Users/batch')
    ->name('users.batch')
    ->middleware(JwtAuthCheck::class)
    ->setOption('meta', ['name' => '用户批量操作', 'group' => '用户']);
