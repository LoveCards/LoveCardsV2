<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::group('all/sender', function () {
    Route::get('types',         'Sender.Sender/types')      ->name('sender.types')        ->setOption('meta', ['name' => '消息驱动列表', 'group' => '消息']);
    Route::get(':type/meta',    'Sender.Sender/meta')       ->name('sender.meta')         ->setOption('meta', ['name' => '驱动配置信息', 'group' => '消息']);
    Route::post('install',      'Sender.Sender/install')    ->name('sender.install')      ->setOption('meta', ['name' => '安装消息驱动', 'group' => '消息']);
    Route::get('channels',      'Sender.Sender/channels')   ->name('sender.channels')     ->setOption('meta', ['name' => '消息渠道列表', 'group' => '消息']);
    Route::get('templates',     'Sender.Sender/templates')  ->name('sender.templates')    ->setOption('meta', ['name' => '消息模板列表', 'group' => '消息']);
    Route::post('test-channel', 'Sender.Sender/testChannel')->name('sender.testChannel')  ->setOption('meta', ['name' => '测试消息渠道', 'group' => '消息']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
