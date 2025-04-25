<?php

namespace app\api\service;

use app\api\model\Users as UsersModel;

use app\common\Common;

use yunarch\app\api\service\IndexUtils;

class Users
{
    /**
     * 用户登录验证函数
     *
     * @param string $account 账号、电子邮件或电话号码
     * @param string $password 密码
     * @return array 登录成功返回用户信息数组，失败返回false
     */
    public static function Login($account, $password): array
    {
        // 尝试使用账号、电子邮件或电话号码查询用
        $result = UsersModel::where('number', $account)
            ->whereOr('email', $account)
            ->whereOr('phone', $account)
            ->find();

        if (!$result) {
            return Common::mArrayEasyReturnStruct('用户不存在', false);
        }

        // 验证密码是否匹配
        if (!password_verify($password, $result['password'])) {
            return Common::mArrayEasyReturnStruct('密码不匹配', false, $result->toArray());
        }

        // 密码匹配，返回用户信息
        return Common::mArrayEasyReturnStruct(null, true, $result->toArray());
    }

    /**
     * 添加用户
     *
     * @param string $number
     * @param string $username
     * @param string $email
     * @param string $phone
     * @param string $password
     * @param int $status
     * @return array
     */
    public static function Register($number, $username, $email, $phone, $password, $status = 0): array
    {
        if ($password != '') {
            if ($email != '') {
                $result = UsersModel::where('email', $email)->find();
            } elseif ($phone != '') {
                $result = UsersModel::where('phone', $phone)->find();
            }
            if ($result) {
                return Common::mArrayEasyReturnStruct('邮箱或手机号已存在', false);
            } else {
                $data = array(
                    'number' => $number,
                    'username' => $username,
                    'email' => $email,
                    'phone' => $phone,
                    'status' => $status,
                );
            }
        } else {
            return Common::mArrayEasyReturnStruct('密码不得为空', false);
        }

        $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        $result = UsersModel::create($data);

        if (!$result) {
            return Common::mArrayEasyReturnStruct('数据插入失败', false);
        }

        return Common::mArrayEasyReturnStruct(null, true, $result->id);
    }

    /**
     * 读取用户列表
     *
     * @return void
     */
    public static function Index($params)
    {
        $index = new IndexUtils(UsersModel::class, $params);
        $result = $index->common('username', ['password'], true);
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result->toArray());
        }
        return Common::mArrayEasyReturnStruct('列表查询失败', false);
    }

    /**
     * 更新指定ID行
     *
     * @param int|array $id 单个ID或ID数组
     * @param array $data
     * @return array
     */
    public static function Patch($id, $data)
    {
        try {
            if (is_array($id)) {
                $result = UsersModel::whereIn('id', $id)->update($data);
            } else {
                $result = UsersModel::update($data, ['id' => $id]);
            }
            return Common::mArrayEasyReturnStruct(null, true, $result);
        } catch (\Throwable $th) {
            return Common::mArrayEasyReturnStruct('更新失败', false, $th);
        }
    }

    /**
     * 批量更新用户数据
     *
     * @param array $data 包含多个用户数据的数组，每个元素必须包含id
     * @return array
     */
    public static function BatchPatch($data)
    {
        try {
            $result = UsersModel::saveAll($data);
            return Common::mArrayEasyReturnStruct(null, true, $result);
        } catch (\Throwable $th) {
            return Common::mArrayEasyReturnStruct('批量更新失败', false, $th);
        }
    }

    /**
     * 读取指定ID行
     *
     * @param int $id
     * @param array $without
     * @return array['status','msg','data'=>object]
     */
    public static function Get($id, $without = [])
    {
        $withoutField = UsersModel::getWithoutField();
        $withoutField[] = 'password';
        $withoutField = array_merge($withoutField, $without);
        $result = UsersModel::where('id', $id)->withoutField($withoutField)->findOrEmpty();
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result);
        }
        return Common::mArrayEasyReturnStruct('查询失败', false, $result);
    }

    /**
     * 删除指定ID行
     *
     * @param int|array $id 单个ID或ID数组
     * @return array
     */
    public static function Delete($id)
    {
        try {
            $result = UsersModel::destroy($id);
            if ($result) {
                return Common::mArrayEasyReturnStruct(null, true, $result);
            }
            return Common::mArrayEasyReturnStruct('删除失败', false);
        } catch (\Throwable $th) {
            return Common::mArrayEasyReturnStruct('删除失败', false, $th);
        }
    }
}
