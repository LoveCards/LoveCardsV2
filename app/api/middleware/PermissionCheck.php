<?php

namespace app\api\middleware;

use app\api\service\Rbac\RBAC;
use app\api\ApiResponse;

class PermissionCheck
{
    public function handle($request, \Closure $next)
    {
        $routeMeta = request()->rule()->getOption('meta') ?? [];

        // 公开路由 — 直接放行（由路由 meta.public 标记）
        if ($routeMeta['public'] ?? false) {
            return $next($request);
        }

        // 获取路由所需能力
        $requiredCaps = $routeMeta['caps'] ?? [];

        // 无 caps 定义 → 降级为路由名
        if (empty($requiredCaps)) {
            $routeName = request()->rule()->getName();
            $requiredCaps = [$routeName];
        }

        // 获取用户能力
        $caps = RBAC::getUserCapabilities($request->rolesId ?? []);

        // 检查是否满足任一能力
        $hasAccess = false;
        foreach ($requiredCaps as $cap) {
            if (in_array($cap, $caps)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            return ApiResponse::createForbidden('权限不足');
        }

        // 注入能力列表到 request
        $request->caps = $caps;

        return $next($request);
    }
}
