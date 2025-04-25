<?php

use think\facade\Route;
use think\facade\Request;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\JwtAuthLogout;

use app\api\middleware\SessionDebounce;
use app\api\middleware\GeetestCheck;

use yunarch\app\roles\middleware\RolesCheck;

Route::post('user/auth/login', 'user.Auth/Login');
Route::post('user/auth/logout', 'user.Auth/Logout');
Route::post('user/auth/register', 'user.Auth/Register');
Route::post('user/auth/captcha', 'user.Auth/Captcha');

Route::group('', function () {
    //卡片
    Route::get('cards', 'user.Cards/List');
    Route::delete('cards', 'user.Cards/DeleteNew');
    //特殊鉴权
    Route::group('', function () {
        Route::post('cards/add', 'user.Cards/Add');
        Route::post('comments/add', 'user.Comments/Add');
    })->middleware([SessionDebounce::class, GeetestCheck::class]);
    Route::post('cards/good', 'user.Cards/Good');

    //评论
    Route::get('comments', 'user.Comments/List');
    Route::delete('comments', 'user.Comments/DeleteNew');

    //喜欢
    Route::get('likes', 'user.Likes/List');
    Route::delete('likes', 'user.Likes/Delete');

    //用户信息
    Route::patch('user/info', 'user.info/Patch');
    Route::get('user/info', 'user.info/Get');

    Route::post('user/password', 'user.info/PostPassword');
    Route::post('user/email', 'user.info/PostEmail');
    Route::post('user/email-captcha', 'user.info/PostBindEmailCaptcha');
})->middleware([JwtAuthCheck::class, RolesCheck::class]);
