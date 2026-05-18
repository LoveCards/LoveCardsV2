<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;

use app\api\middleware\SessionDebounce;
use app\api\middleware\GeetestCheck;

use app\api\middleware\PermissionCheck;

// 认证端点（不在权限中间件组内，但声明 name + meta 供权限列表展示）
Route::post('user/auth/login', 'user.Auth/Login')
    ->name('user.auth.login')
    ->setOption('meta', ['name' => '用户登录', 'group' => 'user.auth']);

Route::post('user/auth/logout', 'user.Auth/Logout')
    ->name('user.auth.logout')
    ->setOption('meta', ['name' => '用户登出', 'group' => 'user.auth']);

Route::post('user/auth/register', 'user.Auth/Register')
    ->name('user.auth.register')
    ->setOption('meta', ['name' => '用户注册', 'group' => 'user.auth']);

Route::post('user/auth/captcha', 'user.Auth/Captcha')
    ->name('user.auth.captcha')
    ->setOption('meta', ['name' => '获取验证码', 'group' => 'user.auth']);

Route::post('user/auth/guest', 'user.Auth/Guest')
    ->middleware(SessionDebounce::class)
    ->name('user.auth.guest')
    ->setOption('meta', ['name' => '访客登录', 'group' => 'user.auth']);

Route::group('', function () {
    //标签
    Route::get('tags', 'user.Tags/noPaginateIndex')
        ->name('user.tags.index')
        ->setOption('meta', ['name' => '获取标签列表', 'group' => 'user.tags']);

    //卡片
    Route::get('card/images', 'admin.Images/CardIndex')
        ->name('user.card-images.index')
        ->setOption('meta', ['name' => '获取卡片图集', 'group' => 'user.cards']);

    Route::get('cards', 'user.Cards/list')
        ->name('user.cards.index')
        ->setOption('meta', ['name' => '获取卡片列表', 'group' => 'user.cards']);

    Route::post('card/like', 'user.Cards/like')
        ->name('user.cards.like')
        ->setOption('meta', ['name' => '喜欢卡片', 'group' => 'user.cards']);

    //特殊鉴权
    Route::group('', function () {
        Route::post('card/comment', 'user.Cards/createComment')
            ->name('user.cards.comment')
            ->setOption('meta', ['name' => '创建评论', 'group' => 'user.cards']);

        Route::post('card', 'user.Cards/createCard')
            ->name('user.cards.store')
            ->setOption('meta', ['name' => '创建卡片', 'group' => 'user.cards']);
    })->middleware([SessionDebounce::class, GeetestCheck::class]);

    Route::delete('card', 'user.Cards/hideCard')
        ->name('user.cards.destroy')
        ->setOption('meta', ['name' => '删除卡片', 'group' => 'user.cards']);

    Route::delete('comment', 'user.Comments/delete')
        ->name('user.comments.destroy')
        ->setOption('meta', ['name' => '删除评论', 'group' => 'user.comments']);

    Route::delete('like', 'user.Likes/unLike')
        ->name('user.likes.destroy')
        ->setOption('meta', ['name' => '取消喜欢', 'group' => 'user.likes']);

    //评论
    Route::get('comments', 'user.Comments/index')
        ->name('user.comments.index')
        ->setOption('meta', ['name' => '获取评论列表', 'group' => 'user.comments']);

    //喜欢
    Route::get('likes', 'user.Likes/list')
        ->name('user.likes.index')
        ->setOption('meta', ['name' => '获取喜欢列表', 'group' => 'user.likes']);

    //用户信息
    Route::patch('user/info', 'user.info/Patch')
        ->name('user.info.update')
        ->setOption('meta', ['name' => '更新用户信息', 'group' => 'user.info']);

    Route::get('user/info', 'user.info/Get')
        ->name('user.info.show')
        ->setOption('meta', ['name' => '获取用户信息', 'group' => 'user.info']);

    Route::post('user/password', 'user.info/PostPassword')
        ->name('user.info.password')
        ->setOption('meta', ['name' => '修改密码', 'group' => 'user.info']);

    Route::post('user/email', 'user.info/PostEmail')
        ->name('user.info.email')
        ->setOption('meta', ['name' => '绑定邮箱', 'group' => 'user.info']);

    Route::post('user/email-captcha', 'user.info/PostBindEmailCaptcha')
        ->name('user.info.email-captcha')
        ->setOption('meta', ['name' => '获取邮箱验证码', 'group' => 'user.info']);
})->middleware([JwtAuthCheck::class, PermissionCheck::class]);
