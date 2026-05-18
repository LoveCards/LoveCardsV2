<?php

namespace app\api\model;

use think\Model;

class RolePermissions extends Model
{
    protected $autoWriteTimestamp = 'timestamp';
    protected $createTime = 'created_at';
    protected $updateTime = false;

    protected $schema = [
        'id' => 'int',
        'role_id' => 'int',
        'permission_hash' => 'string',
        'created_at' => 'timestamp',
    ];

    protected static $withoutField = [
        'created_at'
    ];

    public static function getWithoutField()
    {
        return self::$withoutField;
    }
}
