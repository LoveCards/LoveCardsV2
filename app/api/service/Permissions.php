<?php

namespace app\api\service;

use think\facade\Db;
use app\api\model\Permissions as PermissionsModel;
use app\api\ApiException;
use yunarch\utils\src\ModelList;

class Permissions
{
    public static function Index(array $params): array
    {
        $params['search_default_key'] = 'name';
        $result = ModelList::make(PermissionsModel::class)->getPaginate($params);
        return $result->toArray();
    }

    public static function createPermission(array $data): string
    {
        Db::startTrans();
        try {
            if (isset($data['slug'])) {
                $exists = PermissionsModel::where('slug', $data['slug'])->find();
                if ($exists) {
                    throw ApiException::badRequest('权限标识已存在', ApiException::CODE_PARAM_INVALID);
                }
            }

            if (isset($data['path']) && isset($data['method'])) {
                $exists = PermissionsModel::where('path', $data['path'])
                    ->where('method', $data['method'])
                    ->find();
                if ($exists) {
                    throw ApiException::badRequest('该路径和方法的权限已存在', ApiException::CODE_PARAM_INVALID);
                }
            }

            $result = PermissionsModel::create($data);
            Db::commit();
            return $result->id;
        } catch (\Throwable $th) {
            Db::rollback();
            if ($th instanceof ApiException) {
                throw $th;
            }
            throw ApiException::error('创建权限失败', ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    public static function updatePermission(int $id, array $data): void
    {
        Db::startTrans();
        try {
            if (isset($data['slug'])) {
                $exists = PermissionsModel::where('slug', $data['slug'])
                    ->where('id', '<>', $id)
                    ->find();
                if ($exists) {
                    throw ApiException::badRequest('权限标识已存在', ApiException::CODE_PARAM_INVALID);
                }
            }

            if (isset($data['path']) && isset($data['method'])) {
                $exists = PermissionsModel::where('path', $data['path'])
                    ->where('method', $data['method'])
                    ->where('id', '<>', $id)
                    ->find();
                if ($exists) {
                    throw ApiException::badRequest('该路径和方法的权限已存在', ApiException::CODE_PARAM_INVALID);
                }
            }

            PermissionsModel::update($data, ['id' => $id]);
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            if ($th instanceof ApiException) {
                throw $th;
            }
            throw ApiException::error('更新权限失败', ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    public static function Get(int $id): PermissionsModel
    {
        $permission = PermissionsModel::find($id);
        if (!$permission) {
            throw ApiException::notFound('权限不存在', ApiException::CODE_PERMISSION_NOT_FOUND);
        }
        return $permission;
    }

    public static function deletePermissions($id = false, array $ids = []): void
    {
        if (is_array($id) && isset($id['id'])) {
            $data = is_array($id['id']) ? $id['id'] : [$id['id']];
        } else {
            $data = $id ? (is_array($id) ? $id : [$id]) : $ids;
        }
        
        Db::startTrans();
        try {
            Db::table('role_permissions')
                ->where('permission_id', 'in', $data)
                ->delete();
            
            PermissionsModel::destroy($data);
            
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            throw ApiException::error('删除权限失败', ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    public static function findByPathAndMethod(string $path, string $method): ?PermissionsModel
    {
        return PermissionsModel::where('path', $path)
            ->where('method', $method)
            ->find();
    }

    public static function noPaginateIndex(array $params = []): array
    {
        $params['search_default_key'] = 'name';
        $result = ModelList::make(PermissionsModel::class)->getNoPaginate($params);
        return $result->toArray();
    }
}
