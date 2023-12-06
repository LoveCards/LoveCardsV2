<?php
//TP路由模块
use think\facade\Route;

//404
Route::rule('/404', '/index/Index/error')->append(['code' => 404]);

//Route::rule('/index$', '/Index/index');
//Route::rule('/index/index', '/Index/index');

// Route::rule('/cards$', '/Cards/index');
// Route::rule('/cards/index', '/Cards/index');
// Route::rule('/cards/card', '/Cards/card');
// Route::rule('/cards/search', '/Cards/search');
// Route::rule('/cards/add', '/Cards/add');
// Route::rule('/cards/tag', '/Cards/tag');

//自定义模板
Route::get('/[:Controller]/[:Action]', '/index/Index/Customize');

