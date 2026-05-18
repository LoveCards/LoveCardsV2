<?php

namespace app\api\service\RBAC;

use think\facade\Db;
use think\facade\Cache;
use think\facade\Route;

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
        if (empty($rolesId)) {
            return false;
        }

        if (in_array(1, $rolesId)) {
            return true;
        }

        $hash = md5($routeName . ':' . $method);

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
        if (empty($rolesId)) {
            return [];
        }

        $cacheKey = 'rbac:perms:' . md5(implode(',', $rolesId));
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        $hashes = Db::table('role_permissions')
            ->whereIn('role_id', $rolesId)
            ->column('permission_hash');
        $hashes = array_values(array_unique($hashes));

        $routeMeta = self::getRouteMeta();
        $permissions = [];
        foreach ($hashes as $hash) {
            if (isset($routeMeta[$hash])) {
                $permissions[] = $routeMeta[$hash]['route_name'];
            }
        }
        $permissions = array_values(array_unique($permissions));

        Cache::set($cacheKey, $permissions, 3600);
        return $permissions;
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
     * @return array hash => {route_name, method, name}
     */
    public static function getRouteMeta(): array
    {
        $cacheKey = 'rbac:route_meta';
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

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
                ];
            }
        }

        Cache::set($cacheKey, $result, 3600);
        return $result;
    }

    /**
     * 获取角色的权限 hash 集合（带缓存）
     */
    private static function getRoleHashSet(int $roleId): array
    {
        $cacheKey = 'rbac:set:' . $roleId;
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        $set = Db::table('role_permissions')
            ->where('role_id', $roleId)
            ->column('permission_hash');

        Cache::set($cacheKey, $set, 3600);
        return $set;
    }

    /**
     * 清除角色相关缓存
     */
    public static function clearCache(int $roleId): void
    {
        Cache::clear();
    }
}
