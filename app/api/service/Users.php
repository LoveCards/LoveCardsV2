<?php

namespace app\api\service;

use think\facade\Db;
use app\api\model\Users as UsersModel;

use app\common\Common;

use yunarch\utils\src\ModelList;

class Users
{
    static public function fieldsToggle($fields, $ids, $value1 = [0, 1], $value2 = false): void
    {
        $where = "WHEN {$fields} = {$value1[0]} THEN {$value1[1]} WHEN {$fields} = {$value1[1]} THEN {$value1[0]} ";
        if ($value2) {
            foreach ($value2 as $item) {
                $where = $where . "WHEN {$fields} = {$item} THEN {$value1[1]} ";
            }
        }
        $sql = "CASE {$where}END";

        UsersModel::where('id', 'in', $ids)->update([$fields => Db::raw($sql)]);
    }

    static public function batchOperate($method, $ids): void
    {
        switch ($method) {
            case 'approve':
                self::fieldsToggle('status', $ids, [0, 3], [1, 2]);
                break;
            case 'ban':
                self::fieldsToggle('status', $ids, [0, 1], [2, 3]);
                break;
            case 'hide':
                self::fieldsToggle('status', $ids, [0, 2], [1, 3]);
                break;
            case 'delete':
                self::deleteUsers(false, $ids);
                break;
            default:
                throw \app\api\ApiException::badRequest('方法不存在', \app\api\ApiException::CODE_PARAM_INVALID);
        }
    }

    public static function Login($account, $password): UsersModel
    {
        $result = UsersModel::where('number', $account)
            ->whereOr('username', $account)
            ->whereOr('email', $account)
            ->whereOr('phone', $account)
            ->find();

        if (!$result) {
            throw \app\api\ApiException::unauthorized('用户不存在', \app\api\ApiException::CODE_USER_NOT_FOUND);
        }

        if ($result['status'] != 0 && $result['status'] != 2) {
            throw \app\api\ApiException::forbidden('您的账户已被封禁或未激活', \app\api\ApiException::CODE_USER_BANNED);
        }

        if (!password_verify($password, $result['password'])) {
            throw \app\api\ApiException::unauthorized('密码不匹配', \app\api\ApiException::CODE_PASSWORD_MISMATCH);
        }

        return $result;
    }

    public static function Register($number, $username, $email, $phone, $password, $roles_id = null, $status = 0): UsersModel
    {
        if ($roles_id === null) {
            $roles_id = [config('roles.system_roles.user')];
        }
        if ($password != '') {
            $result = null;
            if ($email != '') {
                $result = UsersModel::where('email', $email)->find();
            } elseif ($phone != '') {
                $result = UsersModel::where('phone', $phone)->find();
            }
            if ($result) {
                throw \app\api\ApiException::badRequest('邮箱或手机号已存在', \app\api\ApiException::CODE_USER_ALREADY_EXISTS);
            } else {
                $data = array(
                    'number' => $number,
                    'username' => $username,
                    'email' => $email,
                    'phone' => $phone,
                    'roles_id' => $roles_id,
                    'status' => $status,
                );
            }
        } else {
            throw \app\api\ApiException::badRequest('密码不得为空', \app\api\ApiException::CODE_PARAM_INVALID);
        }

        $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        $result = UsersModel::create($data);

        return $result;
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

    public static function updateAny($id, $data): UsersModel|int
    {
        return self::Patch($id, $data);
    }

    public static function Get($id, $without = []): UsersModel
    {
        $withoutField = UsersModel::getWithoutField();
        $withoutField[] = 'password';
        $withoutField = array_merge($withoutField, $without);
        return UsersModel::where('id', $id)->withoutField($withoutField)->findOrEmpty();
    }

    static public function deleteUsers($id = false, $ids = []): void
    {
        $data = $id ? $id : $ids;
        UsersModel::destroy($data);
    }

    static public function deleteAny($id = false, $ids = []): void
    {
        self::deleteUsers($id, $ids);
    }
}
