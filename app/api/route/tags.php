<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

// ─── 公开路由 ───
Route::get('tags/:id', 'Content.Tags/get')
    ->name('tags.get')
    ->setOption('meta', ['name' => '标签详情', 'group' => '标签', 'public' => true]);

Route::get('tags', 'Content.Tags/list')
    ->name('tags.list')
    ->setOption('meta', ['name' => '标签列表', 'group' => '标签', 'public' => true]);

// ─── 需要能力的路由（合并 all/ 前缀） ───
Route::group('tags', function () {
    Route::post('', 'Content.Tags/create')
        ->name('tags.create')
        ->setOption('meta', ['name' => '创建标签', 'group' => '标签', 'caps' => ['tags.create']]);

    Route::patch(':id', 'Content.Tags/update')
        ->name('tags.update')
        ->setOption('meta', ['name' => '编辑标签', 'group' => '标签', 'caps' => ['tags.update', 'tags.update.all']]);

    Route::delete(':id', 'Content.Tags/delete')
        ->name('tags.delete')
        ->setOption('meta', ['name' => '删除标签', 'group' => '标签', 'caps' => ['tags.delete', 'tags.delete.all']]);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

// ─── batch 路由（只检查 token） ───
Route::post('tags/batch', 'Content.Tags/batch')
    ->name('tags.batch')
    ->middleware(JwtAuthCheck::class)
    ->setOption('meta', ['name' => '标签批量操作', 'group' => '标签']);
