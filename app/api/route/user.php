<?php

use think\facade\Route;
//use think\facade\Request;

use app\api\middleware\JwtAuthCheck;
//use app\api\middleware\JwtAuthLogout;

use app\api\middleware\SessionDebounce;
use app\api\middleware\GeetestCheck;

use yunarch\app\roles\middleware\RolesCheck;

Route::post('user/auth/login', 'user.Auth/Login');
Route::post('user/auth/logout', 'user.Auth/Logout');
Route::post('user/auth/register', 'user.Auth/Register');
Route::post('user/auth/captcha', 'user.Auth/Captcha');
Route::post('user/auth/guest', 'user.Auth/Guest')->middleware(SessionDebounce::class);

Route::group('', function () {
    //标签
    Route::get('tags', 'user.Tags/noPaginateIndex');

    //卡片
    Route::get('card/images', 'admin.Images/CardIndex'); //卡片图集

    Route::get('cards', 'user.Cards/list'); //卡片列表

    Route::post('card/like', 'user.Cards/like'); //喜欢卡片
    //特殊鉴权
    Route::group('', function () {
        Route::post('card/comment', 'user.Cards/createComment');
        Route::post('card', 'user.Cards/createCard');
    })->middleware([SessionDebounce::class, GeetestCheck::class]);

    Route::delete('card', 'user.Cards/hideCard'); //删除卡片
    Route::delete('comment', 'user.Comments/delete'); //删除评论
    Route::delete('like', 'user.Likes/unLike'); //取消喜欢

    //评论
    Route::get('comments', 'user.Comments/index');

    //喜欢
    Route::get('likes', 'user.Likes/list');

    //用户信息
    Route::patch('user/info', 'user.info/Patch');
    Route::get('user/info', 'user.info/Get');

    Route::post('user/password', 'user.info/PostPassword');
    Route::post('user/email', 'user.info/PostEmail');
    Route::post('user/email-captcha', 'user.info/PostBindEmailCaptcha');
})->middleware([JwtAuthCheck::class, RolesCheck::class]);
