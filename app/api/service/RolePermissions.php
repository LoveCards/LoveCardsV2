<?php

namespace app\api\service;

use think\facade\Db;

use app\api\model\RolePermissions as RolePermissionsModel;
use app\api\model\Roles as RolesModel;
use app\api\model\Permissions as PermissionsModel;

class RolePermissions
{
    /**
     * 为角色添加权限
     *
     * @param int $roleId 角色ID
     * @param int $permissionId 权限ID
     * @return void
     */
    public static function addPermission(int $roleId, int $permissionId): void
    {
        // 检查角色是否存在
        $role = RolesModel::find($roleId);
        if (!$role) {
            throw \app\api\ApiException::createBadRequest('角色不存在', []);
        }

        // 检查权限是否存在
        $permission = PermissionsModel::find($permissionId);
        if (!$permission) {
            throw \app\api\ApiException::createBadRequest('权限不存在', []);
        }

        // 检查关联是否已存在
        $exists = RolePermissionsModel::where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->find();
        if ($exists) {
            throw \app\api\ApiException::createBadRequest('该角色已拥有此权限', []);
        }

        RolePermissionsModel::create([
            'role_id' => $roleId,
            'permission_id' => $permissionId
        ]);
    }

    /**
     * 移除角色的权限
     *
     * @param int $roleId 角色ID
     * @param int $permissionId 权限ID
     * @return void
     */
    public static function removePermission(int $roleId, int $permissionId): void
    {
        $result = RolePermissionsModel::where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->delete();

        if (!$result) {
            throw \app\api\ApiException::createBadRequest('该角色不拥有此权限', []);
        }
    }

    /**
     * 批量添加权限到角色
     *
     * @param int $roleId 角色ID
     * @param array $permissionIds 权限ID数组
     * @return void
     */
    public static function batchAddPermissions(int $roleId, array $permissionIds): void
    {
        // 检查角色是否存在
        $role = RolesModel::find($roleId);
        if (!$role) {
            throw \app\api\ApiException::createBadRequest('角色不存在', []);
        }

        Db::startTrans();
        try {
            foreach ($permissionIds as $permissionId) {
                // 检查权限是否存在
                $permission = PermissionsModel::find($permissionId);
                if (!$permission) {
                    throw \app\api\ApiException::createBadRequest("权限ID {$permissionId} 不存在", []);
                }

                // 检查关联是否已存在
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
            if ($th instanceof \app\api\ApiException) {
                throw $th;
            }
            throw \app\api\ApiException::createError('批量添加权限失败', null, $th);
        }
    }

    /**
     * 批量移除角色的权限
     *
     * @param int $roleId 角色ID
     * @param array $permissionIds 权限ID数组
     * @return void
     */
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
            throw \app\api\ApiException::createError('批量移除权限失败', null, $th);
        }
    }

    /**
     * 检查角色是否拥有指定权限
     *
     * @param int $roleId 角色ID
     * @param int $permissionId 权限ID
     * @return bool
     */
    public static function hasPermission(int $roleId, int $permissionId): bool
    {
        $exists = RolePermissionsModel::where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->find();

        return $exists ? true : false;
    }

    /**
     * 根据路径和方法检查角色是否有权限
     *
     * @param int $roleId 角色ID
     * @param string $path 路径
     * @param string $method HTTP方法
     * @return bool
     */
    public static function checkPermissionByPath(int $roleId, string $path, string $method): bool
    {
        // 先查找权限（支持通配符 *）
        $permission = PermissionsModel::where('path', $path)
            ->where(function ($query) use ($method) {
                $query->where('method', $method)
                    ->whereOr('method', '*');
            })
            ->find();

        if (!$permission) {
            return false;
        }

        // 检查角色是否拥有该权限
        return self::hasPermission($roleId, $permission->id);
    }

    /**
     * 获取角色的所有权限ID
     *
     * @param int $roleId 角色ID
     * @return array
     */
    public static function getRolePermissionIds(int $roleId): array
    {
        $permissions = RolePermissionsModel::where('role_id', $roleId)
            ->column('permission_id');

        return $permissions ?: [];
    }
}

