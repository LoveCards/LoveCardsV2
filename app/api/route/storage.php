<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::group('all/storage', function () {
    Route::get('types', 'Storage/types')->name('storage.types')->setOption('meta', ['name' => '存储驱动列表', 'group' => '存储']);
    Route::get(':type/meta', 'Storage/meta')->name('storage.meta')->setOption('meta', ['name' => '驱动配置信息', 'group' => '存储']);
    Route::post('install', 'Storage/install')->name('storage.install')->setOption('meta', ['name' => '安装存储驱动', 'group' => '存储']);
    Route::get('channels', 'Storage/channels')->name('storage.channels')->setOption('meta', ['name' => '存储渠道列表', 'group' => '存储']);
    Route::post('test-channel', 'Storage/testChannel')->name('storage.testChannel')->setOption('meta', ['name' => '测试存储渠道', 'group' => '存储']);
    Route::get('channel-stats', 'Storage/channelStats')->name('storage.channelStats')->setOption('meta', ['name' => '渠道文件统计', 'group' => '存储']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
