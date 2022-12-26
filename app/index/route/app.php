<?php
//TP路由模块
use think\facade\Route;

//用户资源//登入验证-中间件
Route::group('/', function(){
        Route::rule('User/getAccount', 'User/getAccount');//获取账户信息
        Route::rule('User/getProjectList', 'User/getProjectList');//获取用户项目列表
        Route::rule('User/postPassword', 'User/postPassword');//修改密码
        Route::rule('Project/postProject', 'Project/postProject');//添加项目
        Route::rule('Project/putProject', 'Project/putProject');//修改项目
        Route::rule('Project/deletProject', 'Project/deletProject');//删除项目
    }
)->middleware(app\api\middleware\Identity::class);;

//项目分区列表
Route::rule('/Project/getProjectArea', '/Project/getProjectArea');
//商店项目列表
Route::rule('/Project/getProjectList', '/Project/getProjectList');
//项目详情
Route::rule('/Project/getProject', '/Project/getProject');

//登入
Route::rule('/PassPort/login', '/PassPort/login');
//退出
Route::rule('/PassPort/quit', '/PassPort/quit');
//注册
Route::rule('/PassPort/register', '/PassPort/register');