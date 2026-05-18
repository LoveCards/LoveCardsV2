<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::get('public/cards', 'public.Cards/index')
    ->name('public.cards.index')
    ->setOption('meta', ['name' => '获取卡片列表', 'group' => 'public.cards']);

Route::get('public/hot-cards', 'public.Cards/hotList')
    ->name('public.cards.hot')
    ->setOption('meta', ['name' => '获取热门卡片', 'group' => 'public.cards']);

Route::get('public/tags', 'public.Tags/list')
    ->name('public.tags.index')
    ->setOption('meta', ['name' => '获取标签列表', 'group' => 'public.tags']);

Route::get('theme/config', 'public.theme/Config')
    ->name('theme.config')
    ->setOption('meta', ['name' => '获取主题配置', 'group' => 'theme']);

Route::group('', function () {
    // 文件资源
    Route::post('storage/files', 'public.Upload/upload')
        ->name('storage.files.store')
        ->setOption('meta', ['name' => '上传文件', 'group' => 'storage.files']);

    Route::get('storage/files', 'public.Upload/files')
        ->name('storage.files.index')
        ->setOption('meta', ['name' => '文件列表', 'group' => 'storage.files']);

    Route::delete('storage/files/expired', 'public.Upload/cleanup')
        ->name('storage.files.cleanup')
        ->setOption('meta', ['name' => '清理过期文件', 'group' => 'storage.files']);

    Route::get('storage/files/{id}', 'public.Upload/getFile')
        ->name('storage.files.show')
        ->setOption('meta', ['name' => '查看文件', 'group' => 'storage.files']);

    Route::post('storage/files/batch', 'public.Upload/batchOperate')
        ->name('storage.files.batch')
        ->setOption('meta', ['name' => '批量操作文件', 'group' => 'storage.files']);

    // 直传
    Route::post('storage/files/direct', 'public.Upload/getDirectUploadCredential')
        ->name('storage.files.direct')
        ->setOption('meta', ['name' => '获取直传凭证', 'group' => 'storage.files']);

    Route::patch('storage/files/{id}/confirm', 'public.Upload/confirmDirectUpload')
        ->name('storage.files.confirm')
        ->setOption('meta', ['name' => '确认直传完成', 'group' => 'storage.files']);
})->middleware([JwtAuthCheck::class, PermissionCheck::class]);
