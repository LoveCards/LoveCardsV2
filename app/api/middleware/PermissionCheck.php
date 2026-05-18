<?php

namespace app\api\middleware;

use app\api\service\Users as UsersService;
use app\api\service\RolePermissions as RolePermissionsService;

use app\api\ApiResponse;

class PermissionCheck
{
    public function handle($request, \Closure $next)
    {
        $jwtData = request()->JwtData;

        if (array_key_exists('aid', $jwtData)) {
            return $next($request);
        }

        if (!array_key_exists('uid', $jwtData)) {
            return ApiResponse::createUnauthorized('用户未登录');
        }

        $userData = UsersService::Get($jwtData['uid']);
        if (!$userData || !$userData->id) {
            return ApiResponse::createUnauthorized('用户不存在');
        }

        $userRolesId = json_decode($userData->roles_id, true);
        if (!is_array($userRolesId) || empty($userRolesId)) {
            return ApiResponse::createUnauthorized('用户未分配角色');
        }

        $rule = request()->rule();
        $routeName = $rule ? $rule->getName() : '';
        $currentMethod = strtoupper(request()->method());

        if (empty($routeName)) {
            return $next($request);
        }

        $hasPermission = false;
        foreach ($userRolesId as $roleId) {
            if (RolePermissionsService::checkPermission($roleId, $routeName, $currentMethod)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            return ApiResponse::createUnauthorized('权限不足');
        }

        return $next($request);
    }
}
