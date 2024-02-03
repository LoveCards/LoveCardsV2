<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

use app\common\Common;

class Users extends Model
{
    //开启软删除
    use SoftDelete;
    protected $deleteTime = 'deleted_at';

    //自动时间戳
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    // 设置字段信息
    protected $schema = [
        'id' => 'int',
        'number' => 'string',
        'avatar' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'username' => 'string',
        'password' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'status' => 'int',
    ];

    // 默认排除字段
    protected static $withoutField = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

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
        $result = self::where('number', $account)
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
                $result = self::where('email', $email)->find();
            } elseif ($phone != '') {
                $result = self::where('phone', $phone)->find();
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
        $result = self::create($data);

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
    public static function Index($paginte)
    {
        $result = self::withoutField('password')->paginate([
            'list_rows' => $paginte['list_rows'],
            'page' => $paginte['page'],
        ]);
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result->toArray());
        }
        return Common::mArrayEasyReturnStruct('列表查询失败', false);
    }

    /**
     * 更新指定ID行
     *
     * @param int $id
     * @param array $data
     * @return void
     */
    public static function Patch($id, $data)
    {
        try {
            $result = self::update($data, ['id' => $id]);
            return Common::mArrayEasyReturnStruct(null, true, $result);
        } catch (\Throwable $th) {
            return Common::mArrayEasyReturnStruct('更新失败', false, $th);
        }
    }

    /**
     * Undocumented function
     *
     * @param int $id
     * @param array $without
     * @return array['status','msg','data'=>object]
     */
    public static function Get($id, $without = [])
    {
        $withoutField = self::$withoutField;
        $withoutField[] = 'password';
        $withoutField = array_merge($withoutField, $without);
        $result = self::where('id', $id)->withoutField($withoutField)->findOrEmpty();
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result);
        }
        return Common::mArrayEasyReturnStruct('查询失败', false, $result);
    }

    /**
     * 删除指定ID行
     *
     * @param int $id
     * @return void
     */
    public static function Del($id)
    {
        $result = self::destroy($id);
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result);
        }
        return Common::mArrayEasyReturnStruct('删除失败', false);
    }
}
