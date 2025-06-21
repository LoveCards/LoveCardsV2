<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class Cards extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'new_cards';

    //开启软删除
    use SoftDelete;
    protected $deleteTime = 'deleted_at';

    //自动时间戳
    protected $autoWriteTimestamp = 'timestamp';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    // 设置字段信息
    protected $schema = [
        'id' => 'int',
        'is_top' => 'int',
        'status' => 'int',
        'user_id' => 'int',
        'data' => 'json',
        'cover' => 'varchar',
        'content' => 'text',
        'tags' => '	json',
        'goods' => 'int',
        'views' => 'int',
        'comments' => 'int',
        'post_ip' => 'varchar',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'deleted_at' => 'timestamp'
    ];

    // 默认排除字段
    // protected static $withoutField = [
    //     'deleted_at',
    //     'created_at',
    //     'updated_at'
    // ];

}
