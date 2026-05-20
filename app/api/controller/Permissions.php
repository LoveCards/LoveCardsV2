<?php

namespace app\api\controller;

use app\api\service\RBAC\RBAC;
use app\api\ApiResponse;
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

        $page = (int) Request::param('page', 1);
        $listRows = (int) Request::param('list_rows', 15);
        $search = Request::param('search_value', '');

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
