<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::group('all/sender', function () {
    Route::get('types',         'Sender.Sender/types')      ->name('sender.types');
    Route::get(':type/meta',    'Sender.Sender/meta')       ->name('sender.meta');
    Route::post('install',      'Sender.Sender/install')    ->name('sender.install');
    Route::get('channels',      'Sender.Sender/channels')   ->name('sender.channels');
    Route::get('templates',     'Sender.Sender/templates')  ->name('sender.templates');
    Route::post('test-channel', 'Sender.Sender/testChannel')->name('sender.testChannel');
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
