<?php

namespace app\api\service;

use think\facade\Db;

use app\api\model\Roles as RolesModel;
use app\api\model\RolePermissions as RolePermissionsModel;

use yunarch\utils\src\ModelList;

class Roles
{
    /**
     * 角色列表（分页）
     *
     * @param array $params
     * @return array
     */
    public static function Index(array $params): array
    {
        $params['search_default_key'] = 'name';
        $result = ModelList::make(RolesModel::class)->getPaginate($params);
        return $result->toArray();
    }

    /**
     * 创建角色
     *
     * @param array $data
     * @return string 返回创建的角色ID
     */
    public static function createRole(array $data): string
    {
        Db::startTrans();
        try {
            // 检查slug是否已存在
            if (isset($data['slug'])) {
                $exists = RolesModel::where('slug', $data['slug'])->find();
                if ($exists) {
                    throw \app\api\ApiException::createBadRequest('角色标识已存在', []);
                }
            }

            $result = RolesModel::create($data);
            Db::commit();
            return $result->id;
        } catch (\Throwable $th) {
            Db::rollback();
            if ($th instanceof \app\api\ApiException) {
                throw $th;
            }
            throw \app\api\ApiException::createError('创建角色失败', null, $th);
        }
    }

    /**
     * 更新角色
     *
     * @param int $id 角色ID
     * @param array $data
     * @return void
     */
    public static function updateRole(int $id, array $data): void
    {
        Db::startTrans();
        try {
            // 检查slug是否已存在（排除自己）
            if (isset($data['slug'])) {
                $exists = RolesModel::where('slug', $data['slug'])
                    ->where('id', '<>', $id)
                    ->find();
                if ($exists) {
                    throw \app\api\ApiException::createBadRequest('角色标识已存在', []);
                }
            }

            RolesModel::update($data, ['id' => $id]);
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            if ($th instanceof \app\api\ApiException) {
                throw $th;
            }
            throw \app\api\ApiException::createError('更新角色失败', null, $th);
        }
    }

    /**
     * 获取单个角色
     *
     * @param int $id
     * @return RolesModel
     */
    public static function Get(int $id): RolesModel
    {
        $role = RolesModel::find($id);
        if (!$role) {
            throw \app\api\ApiException::createBadRequest('角色不存在', []);
        }
        return $role;
    }

    /**
     * 删除角色
     *
     * @param int|array $id 单个ID或ID数组或包含id的数组
     * @return void
     */
    public static function deleteRoles($id = false, array $ids = []): void
    {
        // 处理参数：如果是数组且包含id字段，提取id值
        if (is_array($id) && isset($id['id'])) {
            $data = is_array($id['id']) ? $id['id'] : [$id['id']];
        } else {
            $data = $id ? (is_array($id) ? $id : [$id]) : $ids;
        }
        
        Db::startTrans();
        try {
            // 删除角色权限关联
            RolePermissionsModel::where('role_id', 'in', $data)->delete();
            
            // 删除角色
            RolesModel::destroy($data);
            
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            throw \app\api\ApiException::createError('删除角色失败', null, $th);
        }
    }

    /**
     * 为角色分配权限
     *
     * @param int $roleId 角色ID
     * @param array $permissionIds 权限ID数组
     * @return void
     */
    public static function assignPermissions(int $roleId, array $permissionIds): void
    {
        Db::startTrans();
        try {
            // 检查角色是否存在
            $role = RolesModel::find($roleId);
            if (!$role) {
                throw \app\api\ApiException::createBadRequest('角色不存在', []);
            }

            // 删除该角色的所有权限
            RolePermissionsModel::where('role_id', $roleId)->delete();

            // 添加新权限
            foreach ($permissionIds as $permissionId) {
                RolePermissionsModel::create([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId
                ]);
            }

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            if ($th instanceof \app\api\ApiException) {
                throw $th;
            }
            throw \app\api\ApiException::createError('分配权限失败', null, $th);
        }
    }

    /**
     * 获取角色的权限列表
     *
     * @param int $roleId 角色ID
     * @return array
     */
    public static function getRolePermissions(int $roleId): array
    {
        $role = RolesModel::find($roleId);
        if (!$role) {
            throw \app\api\ApiException::createBadRequest('角色不存在', []);
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

