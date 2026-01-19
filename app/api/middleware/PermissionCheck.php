<?php

namespace app\api\middleware;

use think\facade\Request;

use app\api\service\Users as UsersService;
use app\api\service\RolePermissions as RolePermissionsService;

use app\api\ApiResponse;

class PermissionCheck
{
    /**
     * 权限检查中间件
     * 基于数据库的权限系统（roles、permissions、role_permissions表）
     *
     * @param mixed $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $jwtData = request()->JwtData;

        // 兼容旧的校验方式（如果有 aid，说明是管理员，暂时跳过新权限系统）
        if (array_key_exists('aid', $jwtData)) {
            // 旧系统管理员，暂时允许通过
            // TODO: 后续可以迁移到新权限系统
            return $next($request);
        }

        // 新权限系统校验
        if (!array_key_exists('uid', $jwtData)) {
            return ApiResponse::createUnauthorized('用户未登录');
        }

        // 查询用户数据
        $userData = UsersService::Get($jwtData['uid']);
        if (!$userData || !$userData->id) {
            return ApiResponse::createUnauthorized('用户不存在');
        }

        // 获取用户角色ID（JSON格式）
        $userRolesId = json_decode($userData->roles_id, true);
        if (!is_array($userRolesId) || empty($userRolesId)) {
            return ApiResponse::createUnauthorized('用户未分配角色');
        }

        // 获取当前请求的路径和方法
        $currentPath = Request::baseUrl();
        $currentMethod = strtoupper(Request::method());

        // 检查用户的所有角色，只要有一个角色有权限就允许通过
        $hasPermission = false;
        foreach ($userRolesId as $roleId) {
            if (RolePermissionsService::checkPermissionByPath($roleId, $currentPath, $currentMethod)) {
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

