<?php

use think\facade\Route;
//use think\facade\Request;

use app\api\middleware\JwtAuthCheck;
//use app\api\middleware\JwtAuthLogout;

use app\api\middleware\SessionDebounce;
use app\api\middleware\GeetestCheck;

use app\api\middleware\PermissionCheck;

Route::post('user/auth/login', 'user.Auth/Login');
Route::post('user/auth/logout', 'user.Auth/Logout');
Route::post('user/auth/register', 'user.Auth/Register');
Route::post('user/auth/captcha', 'user.Auth/Captcha');
Route::post('user/auth/guest', 'user.Auth/Guest')->middleware(SessionDebounce::class);

Route::group('', function () {
    //标签
    Route::get('tags', 'user.Tags/noPaginateIndex')->name('user.tags.index');

    //卡片
    Route::get('card/images', 'admin.Images/CardIndex')->name('user.card-images.index');

    Route::get('cards', 'user.Cards/list')->name('user.cards.index');

    Route::post('card/like', 'user.Cards/like')->name('user.cards.like');
    //特殊鉴权
    Route::group('', function () {
        Route::post('card/comment', 'user.Cards/createComment')->name('user.cards.comment');
        Route::post('card', 'user.Cards/createCard')->name('user.cards.store');
    })->middleware([SessionDebounce::class, GeetestCheck::class]);

    Route::delete('card', 'user.Cards/hideCard')->name('user.cards.destroy');
    Route::delete('comment', 'user.Comments/delete')->name('user.comments.destroy');
    Route::delete('like', 'user.Likes/unLike')->name('user.likes.destroy');

    //评论
    Route::get('comments', 'user.Comments/index')->name('user.comments.index');

    //喜欢
    Route::get('likes', 'user.Likes/list')->name('user.likes.index');

    //用户信息
    Route::patch('user/info', 'user.info/Patch')->name('user.info.update');
    Route::get('user/info', 'user.info/Get')->name('user.info.show');

    Route::post('user/password', 'user.info/PostPassword')->name('user.info.password');
    Route::post('user/email', 'user.info/PostEmail')->name('user.info.email');
    Route::post('user/email-captcha', 'user.info/PostBindEmailCaptcha')->name('user.info.email-captcha');
})->middleware([JwtAuthCheck::class, PermissionCheck::class]);
