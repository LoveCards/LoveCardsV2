<?php

namespace app\api\service;

use think\facade\Db;
use app\api\model\Users as UsersModel;

use app\common\Common;

use yunarch\utils\src\ModelList;

class Users
{
    protected $UsersModel;

    public function __construct(UsersModel $UsersModel)
    {
        $this->UsersModel = $UsersModel;
    }

    /**
     * 字段反转
     *
     * @param string $fields 字段名
     * @param array $ids ID集
     * @param array $value1 反转值
     * @param array $value2 其他值 比如选项是1 2 3 4那么想要反转3,4那v2就填1,2
     * @return void
     */
    static public function fieldsToggle($fields, $ids, $value1 = [0, 1], $value2 = false): void
    {
        //生成命令
        $where = "WHEN {$fields} = {$value1[0]} THEN {$value1[1]} WHEN {$fields} = {$value1[1]} THEN {$value1[0]} ";
        if ($value2) {
            foreach ($value2 as $item) {
                $where = $where . "WHEN {$fields} = {$item} THEN {$value1[1]} ";
            }
        }
        $sql = "CASE {$where}END";

        UsersModel::where('id', 'in', $ids)->update([$fields => Db::raw($sql)]);
    }

    /**
     * 批量操作
     *
     * @param string $method top：置顶|ban：状态封禁仅自己可见|approve：状态待审核仅自己可见|hide：状态隐藏仅后台可见|delete：删除
     * @param array $ids
     * @return void
     */
    static public function batchOperate($method, $ids): void
    {
        switch ($method) {
            case 'approve':
                self::fieldsToggle('status', $ids, [0, 3], [1, 2]);
            case 'ban':
                self::fieldsToggle('status', $ids, [0, 1], [2, 3]);
            case 'hide':
                self::fieldsToggle('status', $ids, [0, 2], [1, 3]);
                // case 'delete':
                //     return self::deleteTags(false, $ids);
            default:
                throw \app\api\ApiException::createBadRequest('方法不存在', []);
        }
    }
    /**
     * 用户登录验证函数
     *
     * @param string $account 账号、电子邮件或电话号码
     * @param string $password 密码
     * @return UsersModel
     */
    public static function Login($account, $password): UsersModel
    {
        // 尝试使用账号、电子邮件或电话号码查询用
        $result = UsersModel::where('number', $account)
            ->whereOr('email', $account)
            ->whereOr('phone', $account)
            ->find();

        if (!$result) {
            throw \app\api\ApiException::createBadRequest('用户不存在', []);
        }

        if ($result['status'] != 0 && $result['status'] != 2) {
            throw \app\api\ApiException::createBadRequest('您的账户已被封禁或未激活', []);
        }

        // 验证密码是否匹配
        if (!password_verify($password, $result['password'])) {
            throw \app\api\ApiException::createBadRequest('密码不匹配', []);
        }

        // 密码匹配，返回用户信息
        return $result;
    }

    /**
     * 注册用户
     *
     * @param string $number
     * @param string $username
     * @param string $email
     * @param string $phone
     * @param string $password
     * @param int $status
     * @return UsersModel
     */
    public static function Register($number, $username, $email, $phone, $password, $roles_id = [3], $status = 0): UsersModel
    {
        if ($password != '') {
            if ($email != '') {
                $result = UsersModel::where('email', $email)->find();
            } elseif ($phone != '') {
                $result = UsersModel::where('phone', $phone)->find();
            }
            if ($result) {
                throw \app\api\ApiException::createBadRequest('邮箱或手机号已存在', []);
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
            throw \app\api\ApiException::createBadRequest('密码不得为空', []);
        }

        $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        $result = UsersModel::create($data);

        // if (!$result) {
        //     return Common::mArrayEasyReturnStruct('数据插入失败', false);
        // }

        return $result;
    }

    /**
     * 读取用户列表
     *
     * @return array
     */
    public static function Index($params): array
    {
        $params['search_default_key'] = 'username';
        $params['withoutField'] = ['password'];
        $result = ModelList::make(UsersModel::class)->getPaginate($params);

        return $result->toArray();
    }

    /**
     * 更新指定ID行
     *
     * @param int|array $id 单个ID或ID数组
     * @param array $data
     * @return UsersModel
     */
    public static function Patch($id, $data): UsersModel
    {
        if (is_array($id)) {
            $result = UsersModel::whereIn('id', $id)->update($data);
        } else {
            $result = UsersModel::update($data, ['id' => $id]);
        }
        return $result;
    }

    /**
     * 读取指定ID行----------------------------------
     *
     * @param int $id
     * @param array $without
     * @return array['status','msg','data'=>object]
     */
    public static function Get($id, $without = []): UsersModel
    {
        $withoutField = UsersModel::getWithoutField();
        $withoutField[] = 'password';
        $withoutField = array_merge($withoutField, $without);
        return UsersModel::where('id', $id)->withoutField($withoutField)->findOrEmpty();
    }

    /**
     * 删除单&多用户方法
     * * 删除卡片时会同时删除相关的标签、图片和评论
     *
     * @param boolean $id 单用户ID
     * @param array $ids 多用户ID集
     * @return void
     */
    static public function deleteUsers($id = false, $ids = []): void
    {
        $data = $id ? $id : $ids;
        UsersModel::destroy($data);
    }
}
