<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class Cards extends Model
{
    // 设置当前模型对应的完整数据表名称
    //protected $table = 'new_cards';

    //开启软删除
    use SoftDelete;
    protected $deleteTime = 'deleted_at';

    //自动时间戳
    protected $autoWriteTimestamp = 'timestamp';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    // 设置字段信息
    protected $schema = [
        'id' => 'INT',
        'is_top' => 'INT',
        'status' => 'INT',
        'user_id' => 'INT',
        'data' => 'JSON',
        'cover' => 'VARCHAR',
        'content' => 'TEXT',
        'tags' => 'JSON',
        'good' => 'INT',
        'views' => 'INT',
        'comments' => 'INT',
        'post_ip' => 'VARCHAR',
        'created_at' => 'TIMESTAMP',
        'updated_at' => 'TIMESTAMP',
        'deleted_at' => 'TIMESTAMP'
    ];

    // 默认排除字段
    // protected static $withoutField = [
    //     'deleted_at',
    //     'created_at',
    //     'updated_at'
    // ];

}
