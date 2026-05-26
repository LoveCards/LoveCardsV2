<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

// 公开路由（游客可访问）
Route::get('tags/:id', 'Content.Tags/get')->name('tags.get')->setOption('meta', ['name' => '标签详情', 'group' => '标签', 'public' => true]);
Route::get('tags', 'Content.Tags/list')->name('tags.list')->setOption('meta', ['name' => '标签列表', 'group' => '标签', 'public' => true]);

// 用户创建标签
Route::post('tags', 'Content.Tags/create')->name('tags.create')->setOption('meta', ['name' => '创建标签', 'group' => '标签'])->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

// 标签编辑/删除
Route::group('tags', function () {
    Route::patch(':id', 'Content.Tags/update')->name('tags.update')->setOption('meta', ['name' => '编辑标签', 'group' => '标签']);
    Route::delete(':id', 'Content.Tags/delete')->name('tags.delete')->setOption('meta', ['name' => '删除标签', 'group' => '标签']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

// 全部标签（管理员）
Route::group('all/tags', function () {
    Route::get('', 'Content.Tags/allList')->name('tags.allList')->setOption('meta', ['name' => '全部标签', 'group' => '标签']);
    Route::post('', 'Content.Tags/allCreate')->name('tags.allCreate')->setOption('meta', ['name' => '管理创建标签', 'group' => '标签']);
    Route::patch(':id', 'Content.Tags/allUpdate')->name('tags.allUpdate')->setOption('meta', ['name' => '编辑任意标签', 'group' => '标签']);
    Route::delete(':id', 'Content.Tags/allDelete')->name('tags.allDelete')->setOption('meta', ['name' => '删除任意标签', 'group' => '标签']);
    Route::post('batch', 'Content.Tags/batch')->name('tags.batch')->setOption('meta', ['name' => '标签批量操作', 'group' => '标签']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
