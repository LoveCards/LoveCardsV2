<?php

namespace app\api\controller\Auth;

use app\api\service\Auth\RBAC;
use app\api\ApiResponse;
use app\api\controller\BaseController;
use think\facade\Request;

class Permissions extends BaseController
{
    public function all()
    {
        $meta = RBAC::getRouteMeta();
        $result = array_values($meta);
        return ApiResponse::createOk($result);
    }

    public function list()
    {
        $meta = RBAC::getRouteMeta();
        $items = array_values($meta);

        $params = $this->paramIndex(Request::param());
        $search = $params['search_value'] ?? '';
        $page = $params['page'] ?? 1;
        $listRows = $params['list_rows'] ?? 15;

        if (!empty($search)) {
            $items = array_filter($items, function ($item) use ($search) {
                return stripos($item['name'], $search) !== false
                    || stripos($item['route_name'], $search) !== false
                    || stripos($item['method'], $search) !== false
                    || stripos($item['group'], $search) !== false
                    || stripos($item['path'], $search) !== false;
            });
            $items = array_values($items);
        }

        $total = count($items);
        $lastPage = max(1, ceil($total / $listRows));
        $page = min($page, $lastPage);
        $data = array_slice($items, ($page - 1) * $listRows, $listRows);

        return ApiResponse::createOk([
            'current_page' => $page,
            'last_page' => $lastPage,
            'data' => $data,
            'total' => $total,
        ]);
    }
}
