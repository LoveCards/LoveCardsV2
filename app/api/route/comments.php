<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

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
