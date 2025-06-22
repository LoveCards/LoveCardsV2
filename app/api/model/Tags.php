<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

use app\common\Common;

class Tags extends Model
{
    protected $name = 'tags';
    protected $pk = 'id';

    //开启软删除
    use SoftDelete;
    protected $deleteTime = 'deleted_at';

    //自动时间戳
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    // 设置字段信息
    protected $schema = [
        'id' => 'int',
        'aid' => 'int',
        'name' => 'varchar',
        'tip' => 'varchar',
        'status' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // 默认排除字段
    // protected static $withoutField = [
    //     'deleted_at',
    //     'created_at',
    //     'updated_at'
    // ];
}
