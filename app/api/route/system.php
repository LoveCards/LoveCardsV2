<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::group('all/system', function () {
    Route::get('themes', 'System.System/themes')->name('system.themes')->setOption('meta', ['name' => '主题列表', 'group' => '系统']);
    Route::post('set-theme', 'System.System/themeSet')->name('system.themeSet')->setOption('meta', ['name' => '设置主题', 'group' => '系统']);
    Route::post('theme-config', 'System.System/themeConfig')->name('system.themeConfig')->setOption('meta', ['name' => '主题配置管理', 'group' => '系统']);
    Route::get('update', 'System.System/update')->name('system.update')->setOption('meta', ['name' => '系统更新', 'group' => '系统']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

Route::get('theme/config', 'System.Theme/config')->name('theme.config')->setOption('meta', ['name' => '主题配置', 'group' => '系统', 'public' => true]);
