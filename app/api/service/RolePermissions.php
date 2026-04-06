<?php

namespace app\api\service;

use think\facade\Db;
use app\api\model\RolePermissions as RolePermissionsModel;
use app\api\model\Roles as RolesModel;
use app\api\model\Permissions as PermissionsModel;
use app\api\ApiException;

class RolePermissions
{
    public static function addPermission(int $roleId, int $permissionId): void
    {
        $role = RolesModel::find($roleId);
        if (!$role) {
            throw ApiException::notFound('角色不存在', ApiException::CODE_ROLE_NOT_FOUND);
        }

        $permission = PermissionsModel::find($permissionId);
        if (!$permission) {
            throw ApiException::notFound('权限不存在', ApiException::CODE_PERMISSION_NOT_FOUND);
        }

        $exists = RolePermissionsModel::where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->find();
        if ($exists) {
            throw ApiException::badRequest('该角色已拥有此权限', ApiException::CODE_PARAM_INVALID);
        }

        RolePermissionsModel::create([
            'role_id' => $roleId,
            'permission_id' => $permissionId
        ]);
    }

    public static function removePermission(int $roleId, int $permissionId): void
    {
        $result = RolePermissionsModel::where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->delete();

        if (!$result) {
            throw ApiException::badRequest('该角色不拥有此权限', ApiException::CODE_PARAM_INVALID);
        }
    }

    public static function batchAddPermissions(int $roleId, array $permissionIds): void
    {
        $role = RolesModel::find($roleId);
        if (!$role) {
            throw ApiException::notFound('角色不存在', ApiException::CODE_ROLE_NOT_FOUND);
        }

        Db::startTrans();
        try {
            foreach ($permissionIds as $permissionId) {
                $permission = PermissionsModel::find($permissionId);
                if (!$permission) {
                    throw ApiException::notFound("权限ID {$permissionId} 不存在", ApiException::CODE_PERMISSION_NOT_FOUND);
                }

                $exists = RolePermissionsModel::where('role_id', $roleId)
                    ->where('permission_id', $permissionId)
                    ->find();
                if (!$exists) {
                    RolePermissionsModel::create([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId
                    ]);
                }
            }

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            if ($th instanceof ApiException) {
                throw $th;
            }
            throw ApiException::error('批量添加权限失败', ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    public static function batchRemovePermissions(int $roleId, array $permissionIds): void
    {
        Db::startTrans();
        try {
            RolePermissionsModel::where('role_id', $roleId)
                ->where('permission_id', 'in', $permissionIds)
                ->delete();

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            throw ApiException::error('批量移除权限失败', ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    public static function hasPermission(int $roleId, int $permissionId): bool
    {
        $exists = RolePermissionsModel::where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->find();

        return $exists ? true : false;
    }

    public static function checkPermissionByPath(int $roleId, string $path, string $method): bool
    {
        $permission = PermissionsModel::where('path', $path)
            ->where(function ($query) use ($method) {
                $query->where('method', $method)
                    ->whereOr('method', '*');
            })
            ->find();

        if (!$permission) {
            return false;
        }

        return self::hasPermission($roleId, $permission->id);
    }

    public static function getRolePermissionIds(int $roleId): array
    {
        $permissions = RolePermissionsModel::where('role_id', $roleId)
            ->column('permission_id');

        return $permissions ?: [];
    }
}
