<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;
use app\api\service\Captcha\Middleware\CaptchaCheck;

Route::get('cards/hot', 'Content.Cards/hotList')->name('cards.hot')->setOption('meta', ['name' => '热门卡片', 'group' => '卡片', 'public' => true]);
Route::get('cards/:id', 'Content.Cards/get')->name('cards.get')->setOption('meta', ['name' => '卡片详情', 'group' => '卡片', 'public' => true]);
Route::get('cards', 'Content.Cards/list')->name('cards.list')->setOption('meta', ['name' => '卡片列表', 'group' => '卡片', 'public' => true]);

Route::group('cards', function () {
    Route::post('', 'Content.Cards/create')->name('cards.create')->setOption('meta', ['name' => '创建卡片', 'group' => '卡片']);
    Route::patch(':id', 'Content.Cards/update')->name('cards.update')->setOption('meta', ['name' => '编辑卡片', 'group' => '卡片']);
    Route::delete(':id', 'Content.Cards/delete')->name('cards.delete')->setOption('meta', ['name' => '删除卡片', 'group' => '卡片']);
    Route::post(':id/like', 'Content.Cards/like')->name('cards.like')->setOption('meta', ['name' => '点赞卡片', 'group' => '卡片']);
})->middleware(JwtAuthCheck::class)->middleware(CaptchaCheck::class, ['type' => 'captcha'])->middleware(PermissionCheck::class);

Route::get('users/me/cards', 'Content.Cards/listOwn')->name('cards.listOwn')->setOption('meta', ['name' => '我的卡片', 'group' => '卡片'])->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

Route::group('all/cards', function () {
    Route::get('', 'Content.Cards/allList')->name('cards.allList')->setOption('meta', ['name' => '全部卡片', 'group' => '卡片']);
    Route::get(':id', 'Content.Cards/allGet')->name('cards.allGet')->setOption('meta', ['name' => '获取任意卡片', 'group' => '卡片']);
    Route::patch(':id', 'Content.Cards/allUpdate')->name('cards.allUpdate')->setOption('meta', ['name' => '编辑任意卡片', 'group' => '卡片']);
    Route::delete(':id', 'Content.Cards/allDelete')->name('cards.allDelete')->setOption('meta', ['name' => '删除任意卡片', 'group' => '卡片']);
    Route::post('batch', 'Content.Cards/batch')->name('cards.batch')->setOption('meta', ['name' => '卡片批量操作', 'group' => '卡片']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
