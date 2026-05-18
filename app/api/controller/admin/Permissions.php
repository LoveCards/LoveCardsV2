<?php

namespace app\api\controller\admin;

use app\api\service\RBAC\RBAC;
use app\api\ApiResponse;
use app\api\controller\BaseController;
use think\facade\Request;

class Permissions extends BaseController
{
    // 获取所有权限（从路由扫描）
    public function All()
    {
        $meta = RBAC::getRouteMeta();
        $result = array_values($meta);
        return ApiResponse::createOk($result);
    }

    // 分页列表（从路由扫描，前端分页）
    public function Index()
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
