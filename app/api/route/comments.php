<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

// ─── 公开路由 ───
Route::get('comments/:id', 'Content.Comments/get')
    ->name('comments.get')
    ->setOption('meta', ['name' => '评论详情', 'group' => '评论', 'public' => true]);

// ─── 需要能力的路由（合并 all/ 前缀） ───
Route::group('comments', function () {
    Route::patch(':id', 'Content.Comments/update')
        ->name('comments.update')
        ->setOption('meta', ['name' => '编辑评论', 'group' => '评论', 'caps' => ['comments.update', 'comments.update.all']]);

    Route::delete(':id', 'Content.Comments/delete')
        ->name('comments.delete')
        ->setOption('meta', ['name' => '删除评论', 'group' => '评论', 'caps' => ['comments.delete', 'comments.delete.all']]);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

// ─── batch 路由（只检查 token） ───
Route::post('comments/batch', 'Content.Comments/batch')
    ->name('comments.batch')
    ->middleware(JwtAuthCheck::class)
    ->setOption('meta', ['name' => '评论批量操作', 'group' => '评论']);

// ─── users/me 路由（只需 token） ───
Route::get('users/me/comments', 'Content.Comments/listOwn')
    ->name('comments.listOwn')
    ->middleware(JwtAuthCheck::class)
    ->setOption('meta', ['name' => '我的评论', 'group' => '评论']);
