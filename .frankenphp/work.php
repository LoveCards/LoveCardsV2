<?php

declare(strict_types=1);

namespace think;

require __DIR__ . '/vendor/autoload.php';

while (frankenphp_handle_request(function () {
    $http = (new \think\App())->http;
    $response = $http->run();
    $response->send();
    $http->end($response);
})) {
    // 空循环
}
