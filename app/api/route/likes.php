<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::get('likes', 'Likes/list')->name('likes.list')->setOption('meta', ['name' => '我的点赞', 'group' => '点赞'])->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
Route::delete('likes/:id', 'Likes/unlike')->name('likes.unlike')->setOption('meta', ['name' => '取消点赞', 'group' => '点赞'])->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
