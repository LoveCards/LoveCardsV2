<?php
//TP路由模块
use think\facade\Route;

//404
Route::rule('/404', '/index/Index/error')->append(['code' => 404]);

//路由拦截
Route::get('/[:Level0]/[:Level1]', '/index/Index/Customize');

