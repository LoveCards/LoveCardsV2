<?php

use think\facade\Route;
use think\facade\Request;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\JwtAuthLogout;

use app\api\middleware\SessionDebounce;
use app\api\middleware\GeetestCheck;

use yunarch\app\roles\middleware\RolesCheck;

Route::get('public/cards', 'public.Cards/index'); //卡片列表
Route::get('public/hot-cards', 'public.Cards/hotList'); //卡片列表

Route::get('theme/config', 'public.theme/Config');
//Route::post('upload/image', 'public.Upload/Image');

Route::group('', function () {
    Route::post('upload/user-images', 'public.upload/UserImages');
})->middleware([JwtAuthCheck::class, RolesCheck::class]);