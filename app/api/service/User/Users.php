<?php

namespace app\api\service\User;

use app\api\model\Users as UsersModel;
use app\common\FieldsToggle;

use yunarch\utils\src\ModelList;

class Users
{
    static public function batchOperate($method, $ids): void
    {
        switch ($method) {
            case 'approve':
                FieldsToggle::toggle(UsersModel::class, 'status', $ids, [0, 3], [1, 2]);
                break;
            case 'ban':
                FieldsToggle::toggle(UsersModel::class, 'status', $ids, [0, 1], [2, 3]);
                break;
            case 'hide':
                FieldsToggle::toggle(UsersModel::class, 'status', $ids, [0, 2], [1, 3]);
                break;
            case 'delete':
                self::deleteUsers(false, $ids);
                break;
            default:
                throw \app\api\ApiException::badRequest('方法不存在', \app\api\ApiException::CODE_PARAM_INVALID);
        }
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
