<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

// 系统管理（管理员）
Route::group('all/system', function () {
    Route::get('themes', 'System/themes')->name('system.themes')->setOption('meta', ['name' => '主题列表', 'group' => '系统']);
    Route::post('set-theme', 'System/themeSet')->name('system.themeSet')->setOption('meta', ['name' => '设置主题', 'group' => '系统']);
    Route::post('theme-config', 'System/themeConfig')->name('system.themeConfig')->setOption('meta', ['name' => '主题配置管理', 'group' => '系统']);
    Route::get('update', 'System/update')->name('system.update')->setOption('meta', ['name' => '系统更新', 'group' => '系统']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

// 配置管理（管理员）
Route::group('all/config', function () {
    Route::get('', 'Config/index')->name('config.index')->setOption('meta', ['name' => '获取配置', 'group' => '配置']);
    Route::post('', 'Config/save')->name('config.save')->setOption('meta', ['name' => '保存配置', 'group' => '配置']);
    Route::get('groups', 'Config/groups')->name('config.groups')->setOption('meta', ['name' => '配置组列表', 'group' => '配置']);
    Route::post('init', 'Config/init')->name('config.init')->setOption('meta', ['name' => '初始化配置', 'group' => '配置']);
    Route::post('register', 'Config/register')->name('config.register')->setOption('meta', ['name' => '注册配置', 'group' => '配置']);
    Route::post('reload', 'Config/reload')->name('config.reload')->setOption('meta', ['name' => '重载配置', 'group' => '配置']);
    Route::get('storage-channels', 'Config/storageChannels')->name('config.storageChannels')->setOption('meta', ['name' => '存储渠道', 'group' => '配置']);
    Route::post('test-channel', 'Config/testChannel')->name('config.testChannel')->setOption('meta', ['name' => '测试渠道', 'group' => '配置']);
    Route::get('channel-stats', 'Config/channelStats')->name('config.channelStats')->setOption('meta', ['name' => '渠道统计', 'group' => '配置']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

// 存储管理（管理员）
Route::group('all/storage', function () {
    Route::get('types', 'Storage/types')->name('storage.types')->setOption('meta', ['name' => '存储驱动列表', 'group' => '存储']);
    Route::get(':type/meta', 'Storage/meta')->name('storage.meta')->setOption('meta', ['name' => '驱动配置信息', 'group' => '存储']);
    Route::post('install', 'Storage/install')->name('storage.install')->setOption('meta', ['name' => '安装存储驱动', 'group' => '存储']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

// 控制台（管理员）
Route::get('all/dashboard', 'Dashboard/index')->name('dashboard.index')->setOption('meta', ['name' => '控制台', 'group' => '系统'])->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

// 公开路由（游客可访问）
Route::get('theme/config', 'Theme/config')->name('theme.config')->setOption('meta', ['name' => '主题配置', 'group' => '系统', 'public' => true]);
