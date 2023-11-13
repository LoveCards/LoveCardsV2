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
        'number' => 'int',
        'username' => 'string',
        'status' => 'string',
        'password' => 'string',
        'phone' => 'string',
        'email' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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
     * @param string $account 用户名、电子邮件或电话号码
     * @param string $password 密码
     * @return array 登录成功返回用户信息数组，失败返回false
     */
    public static function Login($account, $password): array
    {
        // 尝试使用用户名、电子邮件或电话号码查询用户
        $result = self::where('username', $account)
            ->whereOr('email', $account)
            ->whereOr('phone', $account)
            ->find();

        if (!$result) {
            return Common::FunctionOutput('用户不存在', false);
        }

        // 验证密码是否匹配
        if (!password_verify($password, $result['password'])) {
            return Common::FunctionOutput('密码不匹配', false, $result->toArray());
        }

        // 密码匹配，返回用户信息
        return Common::FunctionOutput(null, true, $result->toArray());
    }

    //注册
    public static function Register($number, $username, $email, $phone, $password): array
    {
        if ($password != '') {
            if ($email != '') {
                $result = self::where('email', $email)->find();
            } elseif ($phone != '') {
                $result = self::where('phone', $phone)->find();
            }
            if ($result) {
                return Common::FunctionOutput('邮箱或手机号已存在', false);
            } else {
                $data = array(
                    'number' => $number,
                    'username' => $username,
                    'email' => $email,
                    'phone' => $phone,
                );
            }
        } else {
            return Common::FunctionOutput('密码不得为空', false);
        }

        $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        $result = self::create($data);

        if (!$result) {
            return Common::FunctionOutput('数据插入失败', false);
        }

        return Common::FunctionOutput(null, true, $result->id);
    }

    //读取列表
    public static function Index()
    {
        $result = self::select();
        if ($result) {
            return Common::FunctionOutput(null, true, $result->toArray());
        }
        return Common::FunctionOutput('列表查询失败', false);
    }

    //读取指定行
    public static function Get($id)
    {
        $withoutField = self::$withoutField;
        $withoutField[] = 'password';
        $result = self::where('id', $id)->withoutField($withoutField)->find();
        if ($result) {
            return Common::FunctionOutput(null, true, $result);
        }
        return Common::FunctionOutput('项目查询失败', false);
    }

    //覆盖行
    public static function Put($id, $data)
    {
        $result = self::update($id, $data);
        if ($result) {
            return Common::FunctionOutput(null, true, $result);
        }
        return Common::FunctionOutput('项目更新失败', false);
    }

    //删除行
    public static function Del($id)
    {
        $result = self::delete($id);
        if ($result) {
            return Common::FunctionOutput(null, true, $result);
        }
        return Common::FunctionOutput('项目删除失败', false);
    }
}
