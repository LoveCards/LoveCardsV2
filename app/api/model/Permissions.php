<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class Permissions extends Model
{
    use SoftDelete;
    protected $deleteTime = 'deleted_at';

    protected $autoWriteTimestamp = 'timestamp';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    protected $schema = [
        'id' => 'int',
        'name' => 'string',
        'slug' => 'string',
        'route_name' => 'string',
        'method' => 'string',
        'description' => 'string',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'deleted_at' => 'timestamp',
    ];

    protected static $withoutField = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    public static function getWithoutField()
    {
        return self::$withoutField;
    }
}
