<?php
// [ 应用入口文件 ]
namespace think;

require __DIR__ . '/../vendor/autoload.php';

$app = new App();
$http = $app->http;

// 注册主题引擎全局中间件（必须在 run 之前）
$app->middleware->unshift(\app\frontend\middleware\ThemeBoot::class);

$response = $http->run();

$response->send();

$http->end($response);
