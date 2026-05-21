<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::get('cards', 'Cards/list')->name('cards.list')->setOption('meta', ['name' => '卡片列表', 'group' => '卡片', 'public' => true]);
Route::get('cards/hot', 'Cards/hotList')->name('cards.hot')->setOption('meta', ['name' => '热门卡片', 'group' => '卡片', 'public' => true]);
Route::get('cards/:id', 'Cards/get')->name('cards.get')->setOption('meta', ['name' => '卡片详情', 'group' => '卡片', 'public' => true]);
Route::get('cards/:id/images', 'Cards/images')->name('cards.images')->setOption('meta', ['name' => '卡片图集', 'group' => '卡片', 'public' => true]);

Route::group('cards', function () {
    Route::post('', 'Cards/create')->name('cards.create')->setOption('meta', ['name' => '创建卡片', 'group' => '卡片']);
    Route::patch(':id', 'Cards/update')->name('cards.update')->setOption('meta', ['name' => '编辑卡片', 'group' => '卡片']);
    Route::delete(':id', 'Cards/delete')->name('cards.delete')->setOption('meta', ['name' => '删除卡片', 'group' => '卡片']);
    Route::post(':id/like', 'Cards/like')->name('cards.like')->setOption('meta', ['name' => '点赞卡片', 'group' => '卡片']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

Route::get('users/me/cards', 'Cards/listOwn')->name('cards.listOwn')->setOption('meta', ['name' => '我的卡片', 'group' => '卡片'])->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

Route::group('all/cards', function () {
    Route::get('', 'Cards/allList')->name('cards.allList')->setOption('meta', ['name' => '全部卡片', 'group' => '卡片']);
    Route::get(':id', 'Cards/allGet')->name('cards.allGet')->setOption('meta', ['name' => '获取任意卡片', 'group' => '卡片']);
    Route::patch(':id', 'Cards/allUpdate')->name('cards.allUpdate')->setOption('meta', ['name' => '编辑任意卡片', 'group' => '卡片']);
    Route::delete(':id', 'Cards/allDelete')->name('cards.allDelete')->setOption('meta', ['name' => '删除任意卡片', 'group' => '卡片']);
    Route::post('batch', 'Cards/batch')->name('cards.batch')->setOption('meta', ['name' => '卡片批量操作', 'group' => '卡片']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
