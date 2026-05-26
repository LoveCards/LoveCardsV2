<?php

namespace app\api\controller\Rbac;

use think\facade\Request;

use app\api\service\Rbac\Roles as RolesService;
use app\api\service\Rbac\RBAC;
use app\api\service\System\Config as ConfigService;
use app\common\cache\CacheManager;
use app\api\validate\Roles as RolesValidate;

use app\api\ApiResponse;
use app\api\controller\BaseController;

class Roles extends BaseController
{
    public function list()
    {
        $params = $this->paramIndex(Request::param());
        $result = RolesService::Index($params);
        return ApiResponse::createOk($result);
    }

    public function get($id)
    {
        $result = RolesService::Get((int) $id);
        return ApiResponse::createOk($result);
    }

    public function create()
    {
        $params = $this->param(RolesValidate::class, RolesValidate::$all_scene['create'], Request::param());

        $id = RolesService::createRole($params);
        return ApiResponse::createOk(['id' => $id]);
    }

    public function update($id)
    {
        $params = $this->param(RolesValidate::class, RolesValidate::$all_scene['update'], Request::param());

        $id = (int) $id;
        unset($params['id']);

        RolesService::updateRole($id, $params);
        return ApiResponse::createNoContent();
    }

    public function delete($id)
    {
        RolesService::deleteRoles((int) $id);
        return ApiResponse::createNoContent();
    }

    public function assignPermissions($id)
    {
        $params = $this->param(RolesValidate::class, RolesValidate::$all_scene['assignPermissions'], Request::param());

        $roleId = (int) $id;
        $permissionHashes = json_decode($params['permission_hashes'], true);

        RolesService::assignPermissions($roleId, $permissionHashes);
        return ApiResponse::createNoContent();
    }

    public function getRolePermissions($id)
    {
        $result = RolesService::getRolePermissionHashes((int) $id);
        return ApiResponse::createOk($result);
    }

    /**
     * 重新 seed 系统角色的权限
     * 扫描当前路由，按 all/ 前缀分配角色
     * POST /api/roles/reseed
     */
    public function reseed()
    {
        CacheManager::clearDomain('rbac');
        $routeMeta = RBAC::getRouteMeta();

        $rootHashes = [];
        $adminHashes = [];
        $userHashes = [];

        foreach ($routeMeta as $hash => $meta) {
            if ($meta['public']) {
                continue;
            }

            $rootHashes[] = $hash;

            if (strpos($meta['path'], '/all/') === 0) {
                $adminHashes[] = $hash;
            } else {
                $userHashes[] = $hash;
            }
        }

        $rootHashes = array_unique($rootHashes);
        $adminHashes = array_unique($adminHashes);
        $userHashes = array_unique($userHashes);

        $now = date('Y-m-d H:i:s');
        $rows = [];

        foreach ($rootHashes as $hash) {
            $rows[] = ['role_id' => 1, 'permission_hash' => $hash, 'created_at' => $now];
        }
        foreach ($adminHashes as $hash) {
            $rows[] = ['role_id' => 2, 'permission_hash' => $hash, 'created_at' => $now];
        }
        foreach ($userHashes as $hash) {
            $rows[] = ['role_id' => 3, 'permission_hash' => $hash, 'created_at' => $now];
        }

        \think\facade\Db::startTrans();
        try {
            \think\facade\Db::table('role_permissions')->delete(true);

            foreach ($rows as $row) {
                \think\facade\Db::table('role_permissions')->insert($row);
            }

            \think\facade\Db::commit();

            CacheManager::clearDomain('rbac');
            ConfigService::reload();

            return ApiResponse::createOk([
                'root'  => count($rootHashes),
                'admin' => count($adminHashes),
                'user'  => count($userHashes),
            ]);
        } catch (\Throwable $e) {
            \think\facade\Db::rollback();
            return ApiResponse::createError('Reseed 失败: ' . $e->getMessage());
        }
    }
}
