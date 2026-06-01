<?php

namespace app\api\service\User;

use app\api\model\Users as UsersModel;
use app\api\model\Roles as RolesModel;
use app\common\support\FieldsToggle;
use app\common\support\ModelList;
use app\common\support\OwnershipGuard;

class Users
{
    use OwnershipGuard;

    protected static string $guardModel = UsersModel::class;
    protected static string $guardField = 'id';

    /**
     * 批量操作
     *
     * @param string $method
     * @param array  $ids
     * @param int    $uid
     * @param array  $caps
     */
    public static function batchOperate(string $method, array $ids, int $uid, array $caps): void
    {
        if (empty($ids)) {
            throw \app\api\ApiException::badRequest('未指定要操作的资源');
        }

        $opCaps = [
            'approve' => 'users.update',
            'ban'     => 'users.update',
            'hide'    => 'users.update',
            'delete'  => 'users.delete',
        ];

        $cap = $opCaps[$method] ?? null;
        if (!$cap) {
            throw \app\api\ApiException::badRequest('不支持的操作');
        }

        // 能力 + 归属一体化检查
        self::guardBatch($ids, $uid, $caps, $cap);

        match ($method) {
            'approve' => FieldsToggle::toggle(UsersModel::class, 'status', $ids, [0, 3], [1, 2]),
            'ban' => FieldsToggle::toggle(UsersModel::class, 'status', $ids, [0, 1], [2, 3]),
            'hide' => FieldsToggle::toggle(UsersModel::class, 'status', $ids, [0, 2], [1, 3]),
            'delete' => UsersModel::destroy($ids),
        };
    }

    public static function Index($params): array
    {
        $params['search_default_key'] = 'username';
        $params['withoutField'] = ['password'];
        $result = ModelList::make(UsersModel::class)->getPaginate($params);
        return $result->toArray();
    }

    public static function listAll($params): array
    {
        return self::Index($params);
    }

    public static function Patch($id, $data): UsersModel|int
    {
        if (is_array($id)) {
            $result = UsersModel::whereIn('id', $id)->update($data);
        } else {
            $result = UsersModel::update($data, ['id' => $id]);
        }
        return $result;
    }

    /**
     * 更新用户（带归属检查）
     *
     * @param int   $id
     * @param array $data
     * @param int   $uid
     * @param array $caps
     */
    public static function updateUser(int $id, array $data, int $uid, array $caps): void
    {
        self::guard($id, $uid, $caps, 'users.update');

        // 移除敏感字段
        unset($data['roles_id']);

        UsersModel::update($data, ['id' => $id]);
    }

    /**
     * 更新任意用户（管理员，已有 .all 能力检查由中间件完成）
     */
    public static function updateAny($id, $data): UsersModel|int
    {
        if (isset($data['roles_id']) && is_array($data['roles_id'])) {
            // 校验角色存在性
            $existingRoleIds = RolesModel::whereIn('id', $data['roles_id'])->column('id');
            $invalidRoleIds = array_diff($data['roles_id'], $existingRoleIds);
            if (!empty($invalidRoleIds)) {
                throw \app\api\ApiException::badRequest('角色不存在：' . implode(', ', $invalidRoleIds));
            }

            // 只有 root 才能分配 root 角色
            $rootRoleId = config('system.system_roles.root');
            if (in_array($rootRoleId, $data['roles_id'])) {
                $currentUserRoles = request()->rolesId ?? [];
                if (!in_array($rootRoleId, $currentUserRoles)) {
                    throw \app\api\ApiException::forbidden('只有超级管理员才能分配超级管理员角色');
                }
            }
        }

        return self::Patch($id, $data);
    }

    /**
     * 删除用户（带归属检查）
     *
     * @param int   $id
     * @param int   $uid
     * @param array $caps
     */
    public static function deleteUser(int $id, int $uid, array $caps): void
    {
        self::guard($id, $uid, $caps, 'users.delete');
        UsersModel::destroy($id);
    }

    /**
     * 删除任意用户（管理员）
     */
    public static function deleteAny($id): void
    {
        UsersModel::destroy($id);
    }

    public static function Get($id, $without = []): UsersModel
    {
        $withoutField = UsersModel::getWithoutField();
        $withoutField[] = 'password';
        $withoutField = array_merge($withoutField, $without);
        return UsersModel::where('id', $id)->withoutField($withoutField)->findOrEmpty();
    }
}
