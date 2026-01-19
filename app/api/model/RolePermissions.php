<?php

namespace app\api\model;

use think\Model;

class RolePermissions extends Model
{
    //自动时间戳
    protected $autoWriteTimestamp = 'timestamp';
    protected $createTime = 'created_at';
    protected $updateTime = false; // 此表没有 updated_at 字段

    // 设置字段信息
    protected $schema = [
        'id' => 'int',
        'role_id' => 'int',
        'permission_id' => 'int',
        'created_at' => 'timestamp',
    ];

    // 默认排除字段
    protected static $withoutField = [
        'created_at'
    ];

    // 获取$withoutField
    public static function getWithoutField()
    {
        return self::$withoutField;
    }
}

