<?php

namespace app\api\service;

use think\facade\Db;
use app\api\model\Users as UsersModel;

use app\common\Common;

use yunarch\app\api\service\IndexUtils;

class Users
{

    /**
     * 字段反转
     *
     * @param string $fields 字段名
     * @param array $ids ID集
     * @param array $value1 反转值
     * @param array $value2 其他值 比如选项是1 2 3 4那么想要反转3,4那v2就填1,2
     * @return void
     */
    static public function fieldsToggle($fields, $ids, $value1 = [0, 1], $value2 = false)
    {
        //生成命令
        $where = "WHEN {$fields} = {$value1[0]} THEN {$value1[1]} WHEN {$fields} = {$value1[1]} THEN {$value1[0]} ";
        if ($value2) {
            foreach ($value2 as $item) {
                $where = $where . "WHEN {$fields} = {$item} THEN {$value1[1]} ";
            }
        }
        $sql = "CASE {$where}END";
        // 存储事务
        Db::startTrans();
        try {
            UsersModel::where('id', 'in', $ids)->update([$fields => Db::raw($sql)]);
            Db::commit(); // 提交事务
            return Common::mArrayEasyReturnStruct('更新成功', true);
        } catch (\Throwable $th) {
            Db::rollback(); // 回滚事务
            return Common::mArrayEasyReturnStruct('更新失败', false, $th->getMessage());
        }
    }

    /**
     * 批量操作标签
     *
     * @param string $method top：置顶|ban：状态封禁仅自己可见|approve：状态待审核仅自己可见|hide：状态隐藏仅后台可见|delete：删除
     * @param array $ids
     * @return void
     */
    static public function batchOperate($method, $ids)
    {
        switch ($method) {
            case 'approve':
                return self::fieldsToggle('status', $ids, [0, 3], [1, 2]);
            case 'ban':
                return self::fieldsToggle('status', $ids, [0, 1], [2, 3]);
            case 'hide':
                return self::fieldsToggle('status', $ids, [0, 2], [1, 3]);
                // case 'delete':
                //     return self::deleteTags(false, $ids);
            default:
                return Common::mArrayEasyReturnStruct('方法不存在', false);
        }
    }
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
     * 删除单&多用户方法
     * * 删除卡片时会同时删除相关的标签、图片和评论
     *
     * @param boolean $id 单用户ID
     * @param array $ids 多用户ID集
     * @return void
     */
    static public function deleteUsers($id = false, $ids = [])
    {
        $data = $id ? $id : $ids;
        // 存储事务
        Db::startTrans();
        try {
            UsersModel::destroy($data);

            Db::commit(); // 提交事务
            return Common::mArrayEasyReturnStruct('删除成功', true);
        } catch (\Throwable $th) {
            Db::rollback(); // 回滚事务
            return Common::mArrayEasyReturnStruct('删除失败', false, $th->getMessage());
        }
    }
}
