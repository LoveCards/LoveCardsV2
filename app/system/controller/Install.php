<?php

namespace app\system\controller;

use think\facade\Request;

use app\common\Export;

use app\system\utils\Common;
use app\system\utils\Database;
use app\system\utils\Environment;
use app\system\utils\Rsa;

use jwt\Jwt;

class Install
{

    public function __construct()
    {
        //检查安装状态
        $result = Common::CheckInstallLock();
        if ($result['status'] === false) {
            return Export::Create(null, 500, '勿重复安装');
        }
    }

    //配置数据库
    public function PostDbConfig()
    {
        $hostname = Request::param('hostname');
        $database = Request::param('database');
        $username = Request::param('username');
        $password = Request::param('password');
        $hostport = Request::param('hostport');
        //pass优先级高于force
        $force = boolval(Request::param('force'));
        $pass = boolval(Request::param('pass'));

        //连接数据库验证
        $result = Database::Connect($hostname, $database, $username, $password, $hostport);
        if (is_array($result) && array_key_exists('status', $result)) {
            return Export::Create(null, 500, $result['msg']);
        }

        //更新数据库配置
        $result = Database::UpdataConfig($hostname, $database, $username, $password, $hostport);
        if (!$result) {
            return Export::Create(null, 500, '配置写入失败，请检查权限');
        }

        //跳过导入
        if (!$pass) {
            //强制导入-清空数据库
            if ($force) {
                $result = Database::Clear();
                if ($result['status'] === false) {
                    return Export::Create(null, 500, $result['msg']);
                }
            }
            //导入数据库文件
            $result = Database::ImportSQLFile('../data.sql');
            if ($result['status']) {
                return Export::Create(null, 200);
            } else {
                if ($result['data'] == 1050) {
                    return Export::Create(['导入失败，数据库已存在相关数据'], 201);
                }
            }
        }

        //数据库导入成功
        return Export::Create(null, 200);
    }

    //生成安装锁
    public function PostInstallLock()
    {
        //更新数据库配置
        $result = Common::InstallLock();
        if ($result['status'] === true) {
            //数据库导入成功
            return Export::Create(null, 200);
        }

        return Export::Create(null, 500, $result['msg']);
    }

    //检查环境
    public function GetCheckEnvironment()
    {
        return Export::Create(Environment::Check(), 200);
    }

    //创建公私钥
    public function PostCreateRsa()
    {
        $key = [
            'public' => Request::param('public'),
            'private' => Request::param('private'),
        ];

        //生成还是传入
        if (!$key['public'] && !$key['private']) {
            $key = Rsa::Generate();
            if (!$key) {
                return Export::Create(null, 500, '密钥对生成失败');
            }
        }

        //校验是否可用
        if(!Jwt::VerifyRsa($key['public'], $key['private'])){
            return Export::Create(null, 500, '密钥对不可用');
        }

        //写入文件
        $result = Rsa::UpdataRsa($key['public'], $key['private']);
        if (!$result) {
            return Export::Create(null, 500, '密钥写入失败，请检查权限');
        }
        return Export::Create(null, 200);
    }
}
