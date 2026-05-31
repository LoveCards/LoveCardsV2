<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

// Theme management API (authenticated)
Route::group('all/theme', function () {
    Route::get('list',       'Theme.ThemeManager/list')        ->name('theme.list')       ->setOption('meta', ['name' => '主题列表',   'group' => '主题']);
    Route::post('upload',    'Theme.ThemeManager/upload')      ->name('theme.upload')     ->setOption('meta', ['name' => '上传主题',   'group' => '主题']);
    Route::post('activate',  'Theme.ThemeManager/activate')    ->name('theme.activate')   ->setOption('meta', ['name' => '切换主题',   'group' => '主题']);
    Route::get('config',     'Theme.ThemeManager/config')      ->name('theme.config')     ->setOption('meta', ['name' => '主题配置',   'group' => '主题']);
    Route::put('config',     'Theme.ThemeManager/updateConfig')->name('theme.updateConfig')->setOption('meta', ['name' => '更新配置',   'group' => '主题']);
    Route::post('freeze',    'Theme.ThemeManager/freezeConfig')->name('theme.freeze')     ->setOption('meta', ['name' => '固化配置',   'group' => '主题']);
    Route::delete('delete',  'Theme.ThemeManager/delete')      ->name('theme.delete')     ->setOption('meta', ['name' => '删除主题',   'group' => '主题']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

// Public theme config (no auth)
Route::get('theme/config', 'Theme.ThemeManager/config')
    ->name('theme.publicConfig')
    ->setOption('meta', ['name' => '公开主题配置', 'group' => '主题', 'public' => true]);
