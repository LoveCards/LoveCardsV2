<?php

namespace app\api\service;

use think\facade\Db;

use app\api\model\Permissions as PermissionsModel;

use yunarch\utils\src\ModelList;

class Permissions
{
    /**
     * 权限列表（分页）
     *
     * @param array $params
     * @return array
     */
    public static function Index(array $params): array
    {
        $params['search_default_key'] = 'name';
        $result = ModelList::make(PermissionsModel::class)->getPaginate($params);
        return $result->toArray();
    }

    /**
     * 创建权限
     *
     * @param array $data
     * @return string 返回创建的权限ID
     */
    public static function createPermission(array $data): string
    {
        Db::startTrans();
        try {
            // 检查slug是否已存在
            if (isset($data['slug'])) {
                $exists = PermissionsModel::where('slug', $data['slug'])->find();
                if ($exists) {
                    throw \app\api\ApiException::createBadRequest('权限标识已存在', []);
                }
            }

            // 检查path+method组合是否已存在
            if (isset($data['path']) && isset($data['method'])) {
                $exists = PermissionsModel::where('path', $data['path'])
                    ->where('method', $data['method'])
                    ->find();
                if ($exists) {
                    throw \app\api\ApiException::createBadRequest('该路径和方法的权限已存在', []);
                }
            }

            $result = PermissionsModel::create($data);
            Db::commit();
            return $result->id;
        } catch (\Throwable $th) {
            Db::rollback();
            if ($th instanceof \app\api\ApiException) {
                throw $th;
            }
            throw \app\api\ApiException::createError('创建权限失败', null, $th);
        }
    }

    /**
     * 更新权限
     *
     * @param int $id 权限ID
     * @param array $data
     * @return void
     */
    public static function updatePermission(int $id, array $data): void
    {
        Db::startTrans();
        try {
            // 检查slug是否已存在（排除自己）
            if (isset($data['slug'])) {
                $exists = PermissionsModel::where('slug', $data['slug'])
                    ->where('id', '<>', $id)
                    ->find();
                if ($exists) {
                    throw \app\api\ApiException::createBadRequest('权限标识已存在', []);
                }
            }

            // 检查path+method组合是否已存在（排除自己）
            if (isset($data['path']) && isset($data['method'])) {
                $exists = PermissionsModel::where('path', $data['path'])
                    ->where('method', $data['method'])
                    ->where('id', '<>', $id)
                    ->find();
                if ($exists) {
                    throw \app\api\ApiException::createBadRequest('该路径和方法的权限已存在', []);
                }
            }

            PermissionsModel::update($data, ['id' => $id]);
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            if ($th instanceof \app\api\ApiException) {
                throw $th;
            }
            throw \app\api\ApiException::createError('更新权限失败', null, $th);
        }
    }

    /**
     * 获取单个权限
     *
     * @param int $id
     * @return PermissionsModel
     */
    public static function Get(int $id): PermissionsModel
    {
        $permission = PermissionsModel::find($id);
        if (!$permission) {
            throw \app\api\ApiException::createBadRequest('权限不存在', []);
        }
        return $permission;
    }

    /**
     * 删除权限
     *
     * @param int|array $id 单个ID或ID数组或包含id的数组
     * @return void
     */
    public static function deletePermissions($id = false, array $ids = []): void
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
            Db::table('role_permissions')
                ->where('permission_id', 'in', $data)
                ->delete();
            
            // 删除权限
            PermissionsModel::destroy($data);
            
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            throw \app\api\ApiException::createError('删除权限失败', null, $th);
        }
    }

    /**
     * 根据路径和方法检查权限是否存在
     *
     * @param string $path 路径
     * @param string $method HTTP方法
     * @return PermissionsModel|null
     */
    public static function findByPathAndMethod(string $path, string $method): ?PermissionsModel
    {
        return PermissionsModel::where('path', $path)
            ->where('method', $method)
            ->find();
    }

    /**
     * 获取所有权限（不分页）
     *
     * @param array $params
     * @return array
     */
    public static function noPaginateIndex(array $params = []): array
    {
        $params['search_default_key'] = 'name';
        $result = ModelList::make(PermissionsModel::class)->getNoPaginate($params);
        return $result->toArray();
    }
}

