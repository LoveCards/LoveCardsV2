<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::group('users/me', function () {
    Route::get('', 'User.Profile/get')->name('users.me.get')->setOption('meta', ['name' => '我的信息', 'group' => '用户']);
    Route::patch('', 'User.Profile/update')->name('users.me.update')->setOption('meta', ['name' => '编辑我的信息', 'group' => '用户']);
    Route::post('password', 'User.Profile/password')->name('users.me.password')->setOption('meta', ['name' => '修改密码', 'group' => '用户']);
    Route::post('email', 'User.Profile/email')->name('users.me.email')->setOption('meta', ['name' => '绑定邮箱', 'group' => '用户']);
    Route::post('email-captcha', 'User.Profile/emailCaptcha')->name('users.me.emailCaptcha')->setOption('meta', ['name' => '邮箱验证码', 'group' => '用户']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

Route::group('all/users', function () {
    Route::get('', 'User.Users/allList')->name('users.allList')->setOption('meta', ['name' => '全部用户', 'group' => '用户']);
    Route::get(':id', 'User.Users/allGet')->name('users.allGet')->setOption('meta', ['name' => '获取任意用户', 'group' => '用户']);
    Route::patch(':id', 'User.Users/allUpdate')->name('users.allUpdate')->setOption('meta', ['name' => '编辑任意用户', 'group' => '用户']);
    Route::delete(':id', 'User.Users/allDelete')->name('users.allDelete')->setOption('meta', ['name' => '删除任意用户', 'group' => '用户']);
    Route::post('batch', 'User.Users/batch')->name('users.batch')->setOption('meta', ['name' => '用户批量操作', 'group' => '用户']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
