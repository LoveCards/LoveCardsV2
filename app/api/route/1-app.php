<?php
//TP路由模块
use think\facade\Route;

//管理后台
Route::rule('/login', '/Login/index');
//登入
Route::rule('/PassPort/login', '/PassPort/login');
//退出
Route::rule('/PassPort/quit', '/PassPort/quit');
//注册
Route::rule('/PassPort/register', '/PassPort/register');