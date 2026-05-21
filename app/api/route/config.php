<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::group('all/config', function () {
    Route::get('', 'Config/index')->name('config.list')->setOption('meta', ['name' => '获取配置', 'group' => '配置']);
    Route::post('', 'Config/update')->name('config.update')->setOption('meta', ['name' => '保存配置', 'group' => '配置']);
    Route::get('groups', 'Config/groups')->name('config.groups')->setOption('meta', ['name' => '配置组列表', 'group' => '配置']);
    Route::post('init', 'Config/init')->name('config.init')->setOption('meta', ['name' => '初始化配置', 'group' => '配置']);
    Route::post('register', 'Config/register')->name('config.register')->setOption('meta', ['name' => '注册配置', 'group' => '配置']);
    Route::post('reload', 'Config/reload')->name('config.reload')->setOption('meta', ['name' => '重载配置', 'group' => '配置']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
