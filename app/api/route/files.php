<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::group('files', function () {
    Route::post('', 'Storage.Upload/upload')
        ->name('files.upload')
        ->setOption('meta', ['name' => '上传文件', 'group' => '文件', 'caps' => ['files.upload']]);

    Route::get('', 'Storage.Upload/list')
        ->name('files.list')
        ->setOption('meta', ['name' => '文件列表', 'group' => '文件', 'caps' => ['files.read', 'files.read.all']]);

    Route::post('direct', 'Storage.Upload/direct')
        ->name('files.direct')
        ->setOption('meta', ['name' => '直传凭证', 'group' => '文件', 'caps' => ['files.upload']]);

    Route::delete('expired', 'Storage.Upload/cleanup')
        ->name('files.cleanup')
        ->setOption('meta', ['name' => '清理过期文件', 'group' => '文件', 'caps' => ['files.delete.all']]);

    Route::patch(':id/confirm', 'Storage.Upload/confirm')
        ->name('files.confirm')
        ->setOption('meta', ['name' => '确认直传', 'group' => '文件', 'caps' => ['files.upload']]);

    Route::get(':id', 'Storage.Upload/get')
        ->name('files.get')
        ->setOption('meta', ['name' => '文件详情', 'group' => '文件', 'caps' => ['files.read', 'files.read.all']]);

    Route::delete(':id', 'Storage.Upload/allDelete')
        ->name('files.allDelete')
        ->setOption('meta', ['name' => '删除文件', 'group' => '文件', 'caps' => ['files.delete', 'files.delete.all']]);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

// batch 路由（只检查 token，能力在 Service 层检查）
Route::post('files/batch', 'Storage.Upload/batch')
    ->name('files.batch')
    ->middleware(JwtAuthCheck::class)
    ->setOption('meta', ['name' => '文件批量操作', 'group' => '文件']);
