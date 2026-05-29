<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;
use app\api\middleware\SessionDebounce;
use app\api\service\Captcha\Middleware\CaptchaCheck;

// 公开路由（游客可访问）
Route::get('cards/:id/comments', 'Content.Comments/cardList')->name('comments.cardList')->setOption('meta', ['name' => '卡片评论列表', 'group' => '评论', 'public' => true]);

// 创建评论（需鉴权 + 防抖 + 极验）
Route::post('cards/:id/comments', 'Content.Comments/create')
    ->name('comments.create')
    ->setOption('meta', ['name' => '创建评论', 'group' => '评论'])
    ->middleware(JwtAuthCheck::class)->middleware(SessionDebounce::class)->middleware(CaptchaCheck::class, ['type' => 'captcha'])->middleware(PermissionCheck::class);

// 用户自己的评论
Route::get('users/me/comments', 'Content.Comments/listOwn')->name('comments.listOwn')->setOption('meta', ['name' => '我的评论', 'group' => '评论'])->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

// 单条评论操作
Route::group('comments', function () {
    Route::get(':id', 'Content.Comments/get')->name('comments.get')->setOption('meta', ['name' => '评论详情', 'group' => '评论']);
    Route::patch(':id', 'Content.Comments/update')->name('comments.update')->setOption('meta', ['name' => '编辑评论', 'group' => '评论']);
    Route::delete(':id', 'Content.Comments/delete')->name('comments.delete')->setOption('meta', ['name' => '删除评论', 'group' => '评论']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

// 全部评论（管理员）
Route::group('all/comments', function () {
    Route::get('', 'Content.Comments/allList')->name('comments.allList')->setOption('meta', ['name' => '全部评论', 'group' => '评论']);
    Route::get(':id', 'Content.Comments/allGet')->name('comments.allGet')->setOption('meta', ['name' => '获取任意评论', 'group' => '评论']);
    Route::patch(':id', 'Content.Comments/allUpdate')->name('comments.allUpdate')->setOption('meta', ['name' => '编辑任意评论', 'group' => '评论']);
    Route::delete(':id', 'Content.Comments/allDelete')->name('comments.allDelete')->setOption('meta', ['name' => '删除任意评论', 'group' => '评论']);
    Route::post('batch', 'Content.Comments/batch')->name('comments.batch')->setOption('meta', ['name' => '评论批量操作', 'group' => '评论']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
