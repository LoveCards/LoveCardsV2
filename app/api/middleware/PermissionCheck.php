<?php

namespace app\api\middleware;

use app\api\service\RBAC\RBAC;
use app\api\ApiResponse;

class PermissionCheck
{
    public function handle($request, \Closure $next)
    {
        $routeName = request()->rule() ? request()->rule()->getName() : '';

        if (empty($routeName)) {
            return $next($request);
        }

        $rolesId = $request->rolesId ?? [];
        $method = request()->method();

        if (!RBAC::checkAccess($rolesId, $routeName, $method)) {
            return ApiResponse::createForbidden('权限不足');
        }

        return $next($request);
    }
}
