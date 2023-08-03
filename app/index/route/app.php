<?php
//TP路由模块
use think\facade\Route;

//404
Route::rule('/404', '/index/index/error')->append(['code' => 404]);
