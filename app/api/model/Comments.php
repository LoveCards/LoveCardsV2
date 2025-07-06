<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class Comments extends Model
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
        'id' => 'INT',
        'aid' => 'INT',
        'pid' => 'INT',
        'parent_id' => 'INT',
        'is_top' => 'INT',
        'status' => 'INT',
        'user_id' => 'INT',
        'data' => 'JSON',
        'content' => 'TEXT',
        'goods' => 'INT',
        'post_ip' => 'VARCHAR',
        'created_at' => 'TIMESTAMP',
        'updated_at' => 'TIMESTAMP',
        'deleted_at' => 'TIMESTAMP',
    ];


    // 默认排除字段
    // protected static $withoutField = [
    //     'deleted_at',
    //     'created_at',
    //     'updated_at'
    // ];

}
