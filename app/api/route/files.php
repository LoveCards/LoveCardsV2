<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

// 文件上传（需鉴权）
Route::group('files', function () {
    Route::post('', 'Upload/upload')->name('files.upload')->setOption('meta', ['name' => '上传文件', 'group' => '文件']);
    Route::get('', 'Upload/list')->name('files.list')->setOption('meta', ['name' => '文件列表', 'group' => '文件']);
    Route::post('batch', 'Upload/batch')->name('files.batch')->setOption('meta', ['name' => '文件批量操作', 'group' => '文件']);
    Route::post('direct', 'Upload/direct')->name('files.direct')->setOption('meta', ['name' => '直传凭证', 'group' => '文件']);
    Route::delete('expired', 'Upload/cleanup')->name('files.cleanup')->setOption('meta', ['name' => '清理过期文件', 'group' => '文件']);
    Route::patch(':id/confirm', 'Upload/confirm')->name('files.confirm')->setOption('meta', ['name' => '确认直传', 'group' => '文件']);
    Route::get(':id', 'Upload/get')->name('files.get')->setOption('meta', ['name' => '文件详情', 'group' => '文件']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

// 全部文件（管理员）
Route::group('all/files', function () {
    Route::delete(':id', 'Upload/allDelete')->name('files.allDelete')->setOption('meta', ['name' => '删除文件', 'group' => '文件']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
