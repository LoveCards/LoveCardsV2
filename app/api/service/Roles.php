<?php

namespace app\api\service;

use think\facade\Db;
use app\api\model\Roles as RolesModel;
use app\api\model\RolePermissions as RolePermissionsModel;
use app\api\ApiException;
use yunarch\utils\src\ModelList;

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
        
        Db::startTrans();
        try {
            RolePermissionsModel::where('role_id', 'in', $data)->delete();
            RolesModel::destroy($data);
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            throw ApiException::error('删除角色失败', ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    public static function assignPermissions(int $roleId, array $permissionIds): void
    {
        Db::startTrans();
        try {
            $role = RolesModel::find($roleId);
            if (!$role) {
                throw ApiException::notFound('角色不存在', ApiException::CODE_ROLE_NOT_FOUND);
            }

            RolePermissionsModel::where('role_id', $roleId)->delete();

            foreach ($permissionIds as $permissionId) {
                RolePermissionsModel::create([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId
                ]);
            }

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            if ($th instanceof ApiException) {
                throw $th;
            }
            throw ApiException::error('分配权限失败', ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    public static function getRolePermissions(int $roleId): array
    {
        $role = RolesModel::find($roleId);
        if (!$role) {
            throw ApiException::notFound('角色不存在', ApiException::CODE_ROLE_NOT_FOUND);
        }

        $permissions = Db::table('role_permissions')
            ->alias('rp')
            ->join('permissions p', 'rp.permission_id = p.id')
            ->where('rp.role_id', $roleId)
            ->where('p.deleted_at', null)
            ->field('p.*')
            ->select()
            ->toArray();

        return $permissions;
    }
}
