<?php

namespace app\api\controller;

use app\api\service\Dashboard as DashboardService;
use app\api\ApiResponse;

class Dashboard extends BaseController
{
    public function index()
    {
        $result = DashboardService::index();
        return ApiResponse::createOk($result);
    }
}
