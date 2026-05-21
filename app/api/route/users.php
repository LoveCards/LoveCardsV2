<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::group('users/me', function () {
    Route::get('', 'UserProfile/get')->name('users.me.get')->setOption('meta', ['name' => '我的信息', 'group' => '用户']);
    Route::patch('', 'UserProfile/update')->name('users.me.update')->setOption('meta', ['name' => '编辑我的信息', 'group' => '用户']);
    Route::post('password', 'UserProfile/password')->name('users.me.password')->setOption('meta', ['name' => '修改密码', 'group' => '用户']);
    Route::post('email', 'UserProfile/email')->name('users.me.email')->setOption('meta', ['name' => '绑定邮箱', 'group' => '用户']);
    Route::post('email-captcha', 'UserProfile/emailCaptcha')->name('users.me.emailCaptcha')->setOption('meta', ['name' => '邮箱验证码', 'group' => '用户']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

Route::group('all/users', function () {
    Route::get('', 'Users/allList')->name('users.allList')->setOption('meta', ['name' => '全部用户', 'group' => '用户']);
    Route::get(':id', 'Users/allGet')->name('users.allGet')->setOption('meta', ['name' => '获取任意用户', 'group' => '用户']);
    Route::patch(':id', 'Users/allUpdate')->name('users.allUpdate')->setOption('meta', ['name' => '编辑任意用户', 'group' => '用户']);
    Route::delete(':id', 'Users/allDelete')->name('users.allDelete')->setOption('meta', ['name' => '删除任意用户', 'group' => '用户']);
    Route::post('batch', 'Users/batch')->name('users.batch')->setOption('meta', ['name' => '用户批量操作', 'group' => '用户']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
