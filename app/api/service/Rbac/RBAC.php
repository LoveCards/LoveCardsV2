<?php

namespace app\api\service\Rbac;

use think\facade\Db;
use think\facade\Route;
use app\common\infra\CacheManager;

class RBAC
{
    /**
     * 检查角色集合是否有指定路由权限
     *
     * @param array $rolesId 角色 ID 数组
     * @param string $routeName 路由名
     * @param string $method HTTP 方法
     * @return bool
     */
    public static function checkAccess(array $rolesId, string $routeName, string $method): bool
    {
        $hash = md5($routeName . ':' . $method);
        $meta = self::getRouteMeta();

        // 公开路由 — 直接放行
        if (isset($meta[$hash]) && ($meta[$hash]['public'] ?? false)) {
            return true;
        }

        if (empty($rolesId)) {
            return false;
        }

        if (in_array(config('system.system_roles.root'), $rolesId)) {
            return true;
        }

        foreach ($rolesId as $roleId) {
            $set = self::getRoleHashSet($roleId);
            if (in_array($hash, $set)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取用户所有权限的 route_name 列表
     *
     * @param array $rolesId 角色 ID 数组
     * @return string[]
     */
    public static function getUserPermissions(array $rolesId): array
    {
        $cacheKey = CacheManager::key('rbac', 'perms', md5(implode(',', $rolesId)));

        return CacheManager::get('rbac', $cacheKey, function () use ($rolesId) {
            $routeMeta = self::getRouteMeta();
            $permissions = [];
            foreach ($routeMeta as $hash => $meta) {
                if ($meta['public'] ?? false) {
                    $permissions[] = $meta['route_name'];
                }
            }

            if (!empty($rolesId)) {
                $dbHashes = Db::table('role_permissions')
                    ->whereIn('role_id', $rolesId)
                    ->column('permission_hash');
                $dbHashes = array_values(array_unique($dbHashes));

                foreach ($dbHashes as $hash) {
                    if (isset($routeMeta[$hash])) {
                        $permissions[] = $routeMeta[$hash]['route_name'];
                    }
                }
            }

            return array_values(array_unique($permissions));
        }, CacheManager::TTL_LONG);
    }

    /**
     * 获取角色已分配的权限 hash 列表
     *
     * @param int $roleId
     * @return string[]
     */
    public static function getRoleHashes(int $roleId): array
    {
        return self::getRoleHashSet($roleId);
    }

    /**
     * 扫描路由定义，返回所有权限元数据
     *
     * @return array hash => {route_name, method, name, public, ...}
     */
    public static function getRouteMeta(): array
    {
        return CacheManager::get('rbac', 'rbac:route_meta', function () {
            $result = [];
            $ruleList = Route::getRuleName()->getRuleList();

            foreach ($ruleList as $rule) {
                $name = $rule['name'] ?? '';
                if (empty($name)) {
                    continue;
                }

                $methods = $rule['method'] ?? 'GET';
                $meta = $rule['option']['meta'] ?? [];

                foreach ((array) $methods as $method) {
                    $method = strtoupper($method);
                    if ($method === 'HEAD' || $method === 'OPTIONS') {
                        continue;
                    }
                    $hash = md5($name . ':' . $method);
                    $result[$hash] = [
                        'hash'       => $hash,
                        'route_name' => $name,
                        'method'     => $method,
                        'name'       => $meta['name'] ?? $name,
                        'group'      => $meta['group'] ?? '',
                        'path'       => '/' . ltrim($rule['rule'] ?? '', '/'),
                        'public'     => $meta['public'] ?? false,
                    ];
                }
            }

            return $result;
        }, CacheManager::TTL_LONG);
    }

    /**
     * 获取角色的权限 hash 集合（带缓存）
     */
    private static function getRoleHashSet(int $roleId): array
    {
        $cacheKey = CacheManager::key('rbac', 'set', $roleId);

        return CacheManager::get('rbac', $cacheKey, function () use ($roleId) {
            return Db::table('role_permissions')
                ->where('role_id', $roleId)
                ->column('permission_hash');
        }, CacheManager::TTL_LONG);
    }

    /**
     * 清除角色相关缓存
     */
    public static function clearCache(int $roleId): void
    {
        CacheManager::clearDomain('rbac');
    }
}
