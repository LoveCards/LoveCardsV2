<?php

namespace app\api\controller\System;

use app\api\service\System\Dashboard as DashboardService;
use app\api\ApiResponse;
use app\api\controller\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $result = DashboardService::index();
        return ApiResponse::createOk($result);
    }
}
