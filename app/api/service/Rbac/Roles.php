<?php

namespace app\api\service\Rbac;

use think\facade\Db;
use app\api\model\Roles as RolesModel;
use app\api\model\RoleCapabilities;
use app\api\ApiException;
use app\common\support\ModelList;
use app\common\infra\CacheManager;

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
                    throw ApiException::badRequest('角色标识已存在');
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
            if (!$role) {
                throw ApiException::notFound('角色不存在');
            }

            if ($role->is_system && isset($data['slug']) && $data['slug'] !== $role->slug) {
                throw ApiException::badRequest('系统角色不可修改标识');
            }

            if (isset($data['slug'])) {
                $exists = RolesModel::where('slug', $data['slug'])
                    ->where('id', '<>', $id)
                    ->find();
                if ($exists) {
                    throw ApiException::badRequest('角色标识已存在');
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
            throw ApiException::notFound('角色不存在');
        }
        return $role;
    }

    public static function deleteRoles(int $id): void
    {
        $data = [$id];

        $systemRoles = RolesModel::whereIn('id', $data)
            ->where('is_system', 1)
            ->select();
        if (!$systemRoles->isEmpty()) {
            $names = $systemRoles->column('name');
            throw ApiException::badRequest('系统角色不可删除：' . implode(', ', $names));
        }

        Db::startTrans();
        try {
            RoleCapabilities::where('role_id', 'in', $data)->delete();
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
     * 分配能力（用 capability 字符串数组）
     *
     * @param int      $roleId 角色 ID
     * @param string[] $caps   能力字符串数组
     */
    public static function assignCapabilities(int $roleId, array $caps): void
    {
        Db::startTrans();
        try {
            $role = RolesModel::find($roleId);
            if (!$role) {
                throw ApiException::notFound('角色不存在');
            }

            // 校验能力字符串存在性
            $validCaps = RBAC::getAllCapabilities();
            $invalidCaps = array_diff($caps, array_keys($validCaps));
            if (!empty($invalidCaps)) {
                throw ApiException::badRequest('不存在的能力：' . implode(', ', $invalidCaps));
            }

            RoleCapabilities::where('role_id', $roleId)->delete();

            foreach ($caps as $cap) {
                RoleCapabilities::create([
                    'role_id'    => $roleId,
                    'capability' => $cap,
                ]);
            }

            Db::commit();
            RBAC::clearCache($roleId);
        } catch (\Throwable $th) {
            Db::rollback();
            if ($th instanceof ApiException) {
                throw $th;
            }
            throw ApiException::error('分配能力失败', ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    /**
     * 获取角色的能力列表
     *
     * @param int $roleId
     * @return string[]
     */
    public static function getRoleCapabilities(int $roleId): array
    {
        $role = RolesModel::find($roleId);
        if (!$role) {
            throw ApiException::notFound('角色不存在');
        }

        return RoleCapabilities::where('role_id', $roleId)
            ->column('capability') ?: [];
    }

    /**
     * 重新 seed 所有角色能力
     *
     * @return array 统计信息
     */
    public static function reseed(): array
    {
        CacheManager::clearDomain('rbac');

        $roles = config('system.system_roles');

        // 能力分配矩阵（使用 config 角色 ID）
        $roleCaps = [
            $roles['guest'] => [
                'cards.read',
                'comments.read',
                'tags.read',
                'files.read',
                'likes.create', 'likes.read', 'likes.delete',
            ],
            $roles['user'] => [
                'cards.read', 'cards.create',
                'comments.read', 'comments.create',
                'tags.read', 'tags.create',
                'users.read', 'users.update',
                'files.upload', 'files.read',
                'likes.create', 'likes.read', 'likes.delete',
            ],
            $roles['admin'] => [
                'cards.read', 'cards.read.all', 'cards.create',
                'cards.update.all', 'cards.delete.all',
                'cards.approve', 'cards.approve.all',
                'cards.pin.all',
                'comments.read', 'comments.read.all',
                'comments.update.all', 'comments.delete.all',
                'tags.read', 'tags.read.all', 'tags.create',
                'tags.update.all', 'tags.delete.all',
                'users.read', 'users.read.all',
                'users.update.all', 'users.delete.all',
                'files.upload', 'files.read', 'files.read.all', 'files.delete', 'files.delete.all',
                'likes.create', 'likes.read', 'likes.delete',
                'dashboard.read',
                'config.read', 'config.update', 'config.init', 'config.reload', 'config.register', 'config.deleteKey',
                'storage.read', 'storage.install', 'storage.test',
                'sender.read', 'sender.install', 'sender.test',
                'captcha.read', 'captcha.install',
                'theme.read', 'theme.update', 'theme.upload', 'theme.delete', 'theme.freeze', 'theme.activate',
                'permissions.read',
                'roles.read', 'roles.create', 'roles.update', 'roles.delete', 'roles.assign',
            ],
            $roles['root'] => array_keys(RBAC::getAllCapabilities()),
        ];

        Db::startTrans();
        try {
            Db::table('role_capabilities')->delete(true);

            $rows = [];
            foreach ($roleCaps as $roleId => $caps) {
                foreach ($caps as $cap) {
                    $rows[] = [
                        'role_id'    => $roleId,
                        'capability' => $cap,
                    ];
                }
            }
            RoleCapabilities::insertAll($rows);

            Db::commit();
            CacheManager::clearDomain('rbac');

            return [
                'total' => count($rows),
                'guest' => count($roleCaps[$roles['guest']]),
                'user'  => count($roleCaps[$roles['user']]),
                'admin' => count($roleCaps[$roles['admin']]),
                'root'  => count($roleCaps[$roles['root']]),
            ];
        } catch (\Throwable $e) {
            Db::rollback();
            throw ApiException::error('Reseed 失败', ApiException::CODE_SYSTEM_ERROR, null, $e);
        }
    }
}
