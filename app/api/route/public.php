<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::get('public/cards', 'public.Cards/index');
Route::get('public/hot-cards', 'public.Cards/hotList');
Route::get('public/tags', 'public.Tags/list');

Route::get('theme/config', 'public.theme/Config');

Route::group('', function () {
    // 文件资源
    Route::post('storage/files', 'public.Upload/upload')->name('storage.files.store');
    Route::get('storage/files', 'public.Upload/files')->name('storage.files.index');
    Route::delete('storage/files/expired', 'public.Upload/cleanup')->name('storage.files.cleanup');
    Route::get('storage/files/{id}', 'public.Upload/getFile')->name('storage.files.show');
    Route::post('storage/files/batch', 'public.Upload/batchOperate')->name('storage.files.batch');

    // 直传
    Route::post('storage/files/direct', 'public.Upload/getDirectUploadCredential')->name('storage.files.direct');
    Route::patch('storage/files/{id}/confirm', 'public.Upload/confirmDirectUpload')->name('storage.files.confirm');
})->middleware([JwtAuthCheck::class, PermissionCheck::class]);
