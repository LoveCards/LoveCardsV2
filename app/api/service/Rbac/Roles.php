<?php

namespace app\api\service\Rbac;

use think\facade\Db;
use app\api\model\Roles as RolesModel;
use app\api\model\RolePermissions as RolePermissionsModel;
use app\api\ApiException;
use app\common\support\ModelList;

class Roles
{
    public static function Index(array $params): array
    {
        $params['search_default_key'] = 'name';
        $result = ModelList::make(RolesModel::class)->getPaginate($params);
        return $result->toArray();
    }

    public static function createRole(array $data): string
    {
        Db::startTrans();
        try {
            if (isset($data['slug'])) {
                $exists = RolesModel::where('slug', $data['slug'])->find();
                if ($exists) {
                    throw ApiException::badRequest('角色标识已存在', ApiException::CODE_PARAM_INVALID);
                }
            }

            $result = RolesModel::create($data);
            Db::commit();
            return $result->id;
        } catch (\Throwable $th) {
            Db::rollback();
            if ($th instanceof ApiException) {
                throw $th;
            }
            throw ApiException::error('创建角色失败', ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    public static function updateRole(int $id, array $data): void
    {
        Db::startTrans();
        try {
            $role = RolesModel::find($id);
            if ($role && $role->is_system && isset($data['slug']) && $data['slug'] !== $role->slug) {
                throw ApiException::badRequest('系统角色不可修改标识', ApiException::CODE_PARAM_INVALID);
            }

            if (isset($data['slug'])) {
                $exists = RolesModel::where('slug', $data['slug'])
                    ->where('id', '<>', $id)
                    ->find();
                if ($exists) {
                    throw ApiException::badRequest('角色标识已存在', ApiException::CODE_PARAM_INVALID);
                }
            }

            RolesModel::update($data, ['id' => $id]);
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            if ($th instanceof ApiException) {
                throw $th;
            }
            throw ApiException::error('更新角色失败', ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    public static function Get(int $id): RolesModel
    {
        $role = RolesModel::find($id);
        if (!$role) {
            throw ApiException::notFound('角色不存在', ApiException::CODE_ROLE_NOT_FOUND);
        }
        return $role;
    }

    public static function deleteRoles($id = false, array $ids = []): void
    {
        if (is_array($id) && isset($id['id'])) {
            $data = is_array($id['id']) ? $id['id'] : [$id['id']];
        } else {
            $data = $id ? (is_array($id) ? $id : [$id]) : $ids;
        }

        $systemRoles = RolesModel::whereIn('id', $data)
            ->where('is_system', 1)
            ->select();
        if (!$systemRoles->isEmpty()) {
            $names = $systemRoles->column('name');
            throw ApiException::badRequest('系统角色不可删除：' . implode(', ', $names), ApiException::CODE_PARAM_INVALID);
        }

        Db::startTrans();
        try {
            RolePermissionsModel::where('role_id', 'in', $data)->delete();
            RolesModel::destroy($data);
            Db::commit();

            foreach ($data as $roleId) {
                RBAC::clearCache($roleId);
            }
        } catch (\Throwable $th) {
            Db::rollback();
            if ($th instanceof ApiException) {
                throw $th;
            }
            throw ApiException::error('删除角色失败', ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    /**
     * 分配权限（用 permission_hash 数组）
     */
    public static function assignPermissions(int $roleId, array $permissionHashes): void
    {
        Db::startTrans();
        try {
            $role = RolesModel::find($roleId);
            if (!$role) {
                throw ApiException::notFound('角色不存在', ApiException::CODE_ROLE_NOT_FOUND);
            }

            RolePermissionsModel::where('role_id', $roleId)->delete();

            // 过滤公开路由 hash（公开路由不依赖 role_permissions，无需写入）
            $routeMeta = RBAC::getRouteMeta();
            $permissionHashes = array_filter($permissionHashes, function ($hash) use ($routeMeta) {
                return !($routeMeta[$hash]['public'] ?? false);
            });

            foreach ($permissionHashes as $hash) {
                RolePermissionsModel::create([
                    'role_id' => $roleId,
                    'permission_hash' => $hash,
                ]);
            }

            Db::commit();
            RBAC::clearCache($roleId);
        } catch (\Throwable $th) {
            Db::rollback();
            if ($th instanceof ApiException) {
                throw $th;
            }
            throw ApiException::error('分配权限失败', ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    /**
     * 获取角色的权限 hash 列表
     */
    public static function getRolePermissionHashes(int $roleId): array
    {
        $role = RolesModel::find($roleId);
        if (!$role) {
            throw ApiException::notFound('角色不存在', ApiException::CODE_ROLE_NOT_FOUND);
        }

        return RolePermissionsModel::where('role_id', $roleId)
            ->column('permission_hash') ?: [];
    }
}
