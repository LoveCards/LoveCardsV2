<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::get('public/cards', 'public.Cards/index');
Route::get('public/hot-cards', 'public.Cards/hotList');
Route::get('public/tags', 'public.Tags/list');

Route::get('theme/config', 'public.theme/Config');

Route::group('', function () {
    Route::post('upload/upload', 'public.Upload/upload');
    Route::get('upload/files', 'public.Upload/files');
    Route::get('upload/get-file', 'public.Upload/getFile');
    Route::post('upload/batch-operate', 'public.Upload/batchOperate');
    Route::post('upload/direct-upload-credential', 'public.Upload/getDirectUploadCredential');
    Route::post('upload/direct-upload-confirm', 'public.Upload/confirmDirectUpload');
    Route::post('upload/cleanup', 'public.Upload/cleanup');
})->middleware([JwtAuthCheck::class, PermissionCheck::class]);
