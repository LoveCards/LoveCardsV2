<?php

namespace app\system\controller;

//TP类
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use think\facade\Config;


//类
use app\common\Common;
use app\api\common\Common as ApiCommon;

class Install
{
    //fun Exception的$e方法getData($e)
    //传入错误信息//1045连接信息错误1146表不存在
    protected static function FunGetData($e)
    {
        $data['SQLSTATE'] = $e->getData()['PDO Error Info']['SQLSTATE'];
        $data['Code'] = $e->getData()['PDO Error Info']['Driver Error Code'];
        $data['Message'] = $e->getData()['PDO Error Info']['Driver Error Message'];
        return $data;
    }

    //fun导入sql文件到数据库
    protected static function FunInsert($file)
    {
        try {
            $sql = file_get_contents($file);
        } catch (\Exception $e) {
            return [
                'state' => false,
                'code' => 0,
                'message' => '无法找到sql文件，请检查sql文件是否命名为“data.sql”并存在于程序根目录！'
            ];
        }

        // 分割SQL语句
        $sqlArr = explode(';', $sql);

        try {
            // 执行SQL语句
            foreach ($sqlArr as $sql) {
                if (trim($sql) != '') {
                    if (strpos($sql, 'CREATE TABLE') !== false) {
                        Db::query($sql);
                    } else {
                        Db::execute($sql);
                    }
                }
            }
        } catch (\Exception $e) {
            return [
                'state' => false,
                'code' => Install::FunGetData($e)['Code'],
                'message' => Install::FunGetData($e)['Message']
            ];
        }
        return [
            'state' => true,
            'code' => 1,
            'message' => 'success'
        ];
    }

    //fun更新数据库配置文件
    protected static function FunUpdataDb($hostname, $database, $username, $password, $hostport)
    {
        $filename = '../config/database.php';
        $str_file = file_get_contents($filename);
        $data = [
            'hostname' => $hostname,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'hostport' => $hostport,
        ];

        foreach ($data as $key => $value) {
            //构建正则匹配
            $pattern = "/env\('database\." . $key . "',\s*'([^']*)'\)/";
            //判断是否成功匹配
            if (preg_match($pattern, $str_file)) {
                //匹配成功更新
                $str_file = preg_replace($pattern, "env('database." . $key . "', '" . $value . "')", $str_file);
            }
        }

        //写入并返回结果
        if (!file_put_contents($filename, $str_file)) {
            return false;
        } else {
            return true;
        }
    }

    //验证安装环境
    protected static function DataVerifyEnvironment()
    {
        $IE = Common::systemVer()['InstallEnvironment'];
        $data = [
            'php' => [
                'v' => phpversion(),
                's' => (($IE['php']['f'] <= phpversion()) && (phpversion() <= $IE['php']['l'])),
            ],
            'pdo_mysql' => extension_loaded('pdo')
        ];
        return $data;
    }

    //api
    public function apiVerifyEnvironment(){
        return Common::create(Install::DataVerifyEnvironment(), '验证结果', 200);
    }
    public function apiSetDbConfig()
    {
        //待完成-验证是否已经安装

        $hostname = Request::param('hostname');
        $database = Request::param('database');
        $username = Request::param('username');
        $password = Request::param('password');
        $hostport = Request::param('hostport');

        //动态更新数据库信息
        Config::set([
            'connections' => [
                'mysql' => [
                    'type' => 'mysql',
                    'hostname' => $hostname,
                    'database' => $database,
                    'username' => $username,
                    'password' => $password,
                    'hostport' => $hostport,
                    'params' => [],
                    'charset' => 'utf8',
                    'prefix' => '',
                    'deploy' => 0,
                    'rw_separate' => false,
                    'master_num' => 1,
                    'slave_no' => '',
                    'fields_strict' => true,
                    'break_reconnect' => false,
                    'trigger_sql' => true,
                    'fields_cache' => false,
                ]
            ]
        ], 'database');

        //先判断数据库信息是否正确
        try {
            Db::connect()->table('system')->where('name', 'LCEAPI')->find();
        } catch (\Exception $e) {
            $result = Install::FunGetData($e);
            if ($result['Code'] == 1045) {
                return Common::create([], '连接出错，请检查数据库信息是否存在错误', 403);
            }
        }
        //信息无误写入配置文件
        if (!Install::FunUpdataDb($hostname, $database, $username, $password, $hostport)) {
            return Common::create([], '配置文件写入失败', 403);
        }
        //写入数据库
        $result = Install::FunInsert('../data.sql');
        if ($result['state']) {
            return Common::create([], '数据库导入成功', 200);
        } else {
            if ($result['code'] == 1050) {
                return Common::create([], '数据库貌似已经存在，是否跳过该步骤', 201);
            }
        }

        return Common::create($result, '数据库配置出现未知错误', 500);
    }
    //生成记录
    public function apiSetInstallLock()
    {
        if (@fopen("../lock.txt", 'r')) {
            return Common::create([], '安装记录已存在', 200);
        } else {
            if (file_put_contents("../lock.txt", "LoveCards.cn")) {
                return Common::create([], '安装记录已生成', 200);
            } else {
                return Common::create([], '安装记录生成失败，请手动添加lock.txt文件到根目录！', 500);
            }
        }
    }

    //输出
    public function index()
    {

        //安装检测
        @$file = fopen("../lock.txt", "r");
        if ($file) {
            return Common::jumpUrl('/index/index', '检测到安装记录，如需重新安装请删除根目录[lock.txt]文件');
            exit;
        }

        //基础变量
        View::assign([
            'systemVer' => Common::systemVer(),
            'verifyEnvironment' => Install::DataVerifyEnvironment(),
            'viewTitle'  => '安装',
            'viewDescription' => false,
            'viewKeywords' => false
        ]);

        //输出模板
        return View::fetch('/install');
    }
}
