<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

use app\common\Common;

class Images extends Model
{
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
        'aid' => 'int',
        'pid' => 'int',
        'user_id' => 'int',
        'url' => 'string',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'deleted_at' => 'timestamp'
    ];

    // 默认排除字段
    protected static $withoutField = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];
}
