<?php

namespace app\api\controller\Rbac;

use app\api\service\Rbac\RBAC;
use app\api\ApiResponse;
use app\api\controller\BaseController;
use think\facade\Request;

class Permissions extends BaseController
{
    /**
     * 获取全量能力列表（capability 字符串 + 描述）
     */
    public function all()
    {
        $allCaps = RBAC::getAllCapabilities();
        $result = [];
        foreach ($allCaps as $cap => $desc) {
            $result[] = ['capability' => $cap, 'description' => $desc];
        }
        return ApiResponse::createOk($result);
    }

    /**
     * 分页搜索能力列表
     */
    public function list()
    {
        $allCaps = RBAC::getAllCapabilities();
        $items = [];
        foreach ($allCaps as $cap => $desc) {
            $items[] = ['capability' => $cap, 'description' => $desc];
        }

        $params = $this->paramIndex(Request::param());
        $search = $params['search_value'] ?? '';
        $page = $params['page'] ?? 1;
        $listRows = $params['list_rows'] ?? 15;

        if (!empty($search)) {
            $items = array_filter($items, function ($item) use ($search) {
                return stripos($item['capability'], $search) !== false
                    || stripos($item['description'], $search) !== false;
            });
            $items = array_values($items);
        }

        $total = count($items);
        $lastPage = max(1, ceil($total / $listRows));
        $page = min($page, $lastPage);
        $data = array_slice($items, ($page - 1) * $listRows, $listRows);

        return ApiResponse::createOk([
            'current_page' => $page,
            'last_page'    => $lastPage,
            'data'         => $data,
            'total'        => $total,
        ]);
    }
}
