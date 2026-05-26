<?php

namespace app\api\middleware;

use app\api\service\Auth\RBAC;
use app\api\ApiResponse;

class PermissionCheck
{
    public function handle($request, \Closure $next)
    {
        $routeName = request()->rule() ? request()->rule()->getName() : '';

        if (empty($routeName)) {
            return ApiResponse::createForbidden('路由未定义');
        }

        $rolesId = $request->rolesId ?? [];
        $method = request()->method();

        if (!RBAC::checkAccess($rolesId, $routeName, $method)) {
            return ApiResponse::createForbidden('权限不足');
        }

        return $next($request);
    }
}
