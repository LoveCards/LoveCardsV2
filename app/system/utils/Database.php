<?php

namespace app\system\utils;

use think\facade\Db;
use think\facade\Config;

use app\system\utils\Common;

class Database
{

    public static $DatabaseConfigPath = '../config/database.php';

    //fun Exception的$e方法getData($e)
    //传入错误信息//1045连接信息错误1146表不存在
    protected static function getErrorData($e)
    {
        $data['SQLSTATE'] = $e->getData()['PDO Error Info']['SQLSTATE'];
        $data['Code'] = $e->getData()['PDO Error Info']['Driver Error Code'];
        $data['Message'] = $e->getData()['PDO Error Info']['Driver Error Message'];
        return $data;
    }

    /**
     * 链接数据库
     *
     * @param string $hostname
     * @param string $database
     * @param string $username
     * @param string $password
     * @param string $hostport
     * @return array|object 返回标准结构或数据库对象
     */
    public static function Connect($hostname, $database, $username, $password, $hostport)
    {
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
            $result = Db::connect()->table('admin')->where('id', 1)->find();
            return $result;
        } catch (\Exception $e) {
            $result = self::getErrorData($e);
            if ($result['Code'] == 1045) {
                return Common::mArrayEasyReturnStruct('连接出错，请检查数据库信息是否存在错误', false);
            }
        }
    }

    /**
     * 清空数据库
     *
     * @return array
     */
    public static function Clear()
    {
        // 获取数据库连接
        $connection = Db::connect();
        // 获取数据库名
        $database = $connection->getConfig('database');
        // 获取数据库中所有表名
        $tables = $connection->getTables($database);
        // 遍历所有表并删除
        try {
            foreach ($tables as $table) {
                Db::execute("DROP TABLE IF EXISTS `{$table}`");
            }
            return Common::mArrayEasyReturnStruct('清空成功', true);
        } catch (\Throwable $th) {
            return Common::mArrayEasyReturnStruct('清空成功', false);
        }
    }

    /**
     * 更新数据库配置文件
     *
     * @param string $hostname
     * @param string $database
     * @param string $username
     * @param string $password
     * @param string $hostport
     * @return bool
     */
    public static function UpdataConfig($hostname, $database, $username, $password, $hostport)
    {
        $filename = self::$DatabaseConfigPath;
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
        try {
            $result = file_put_contents($filename, $str_file);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * 导入数据库
     *
     * @param [type] $file
     * @return void
     */
    public static function ImportSQLFile($file)
    {
        try {
            $sql = file_get_contents($file);
        } catch (\Exception $e) {
            return  Common::mArrayEasyReturnStruct('无法找到sql文件，请检查sql文件是否命名为“data.sql”并存在于程序根目录！', false);
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
            return  Common::mArrayEasyReturnStruct(self::getErrorData($e)['Message'], false, self::getErrorData($e)['Code']);
        }
        return Common::mArrayEasyReturnStruct('导入成功', true);
    }
}
