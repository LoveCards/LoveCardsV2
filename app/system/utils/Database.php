<?php

namespace app\system\utils;

use think\facade\Db;
use think\facade\Config;
use app\api\ApiException;

class Database
{
    public static $DatabaseConfigPath = '../config/database.php';

    protected static function getErrorData($e)
    {
        $data['SQLSTATE'] = $e->getData()['PDO Error Info']['SQLSTATE'];
        $data['Code'] = $e->getData()['PDO Error Info']['Driver Error Code'];
        $data['Message'] = $e->getData()['PDO Error Info']['Driver Error Message'];
        return $data;
    }

    public static function Connect($hostname, $database, $username, $password, $hostport)
    {
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

        try {
            return Db::connect()->table('admin')->where('id', 1)->find();
        } catch (\Exception $e) {
            $result = self::getErrorData($e);
            if ($result['Code'] == 1045) {
                throw ApiException::error('连接出错，请检查数据库信息是否存在错误', ApiException::CODE_SYSTEM_ERROR);
            }
            throw ApiException::error('数据库连接失败', ApiException::CODE_SYSTEM_ERROR);
        }
    }

    public static function Clear(): bool
    {
        $connection = Db::connect();
        $database = $connection->getConfig('database');
        $tables = $connection->getTables($database);
        try {
            foreach ($tables as $table) {
                Db::execute("DROP TABLE IF EXISTS `{$table}`");
            }
            return true;
        } catch (\Throwable $th) {
            throw ApiException::error('清空数据库失败', ApiException::CODE_SYSTEM_ERROR);
        }
    }

    public static function UpdataConfig($hostname, $database, $username, $password, $hostport): bool
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
            $pattern = "/env\('database\." . $key . "',\s*'([^']*)'\)/";
            if (preg_match($pattern, $str_file)) {
                $str_file = preg_replace($pattern, "env('database." . $key . "', '" . $value . "')", $str_file);
            }
        }

        try {
            file_put_contents($filename, $str_file);
            return true;
        } catch (\Throwable $th) {
            throw ApiException::error('配置文件写入失败', ApiException::CODE_SYSTEM_ERROR);
        }
    }

    public static function ImportSQLFile($file): void
    {
        try {
            $sql = file_get_contents($file);
        } catch (\Exception $e) {
            throw ApiException::error('无法找到sql文件，请检查sql文件是否命名为"data.sql"并存在于程序根目录！', ApiException::CODE_PARAM_INVALID);
        }

        $sqlArr = explode(';', $sql);

        try {
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
            $errorData = self::getErrorData($e);
            throw ApiException::error($errorData['Message'], ApiException::CODE_SYSTEM_ERROR);
        }
    }
}
