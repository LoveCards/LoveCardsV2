<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class TagsMap extends Model
{
    //开启软删除
    use SoftDelete;
    protected $deleteTime = 'deleted_at';

    //自动时间戳
    protected $autoWriteTimestamp = 'timestamp';
    protected $createTime = 'created_at';

    // 设置字段信息
    protected $schema = [
        'id' => 'INT',
        'aid' => 'INT',
        'pid' => 'INT',
        'tag_id' => 'INT',
        'status' => 'INT',
        'created_at' => 'TIMESTAMP',
        'deleted_at' => 'TIMESTAMP',
    ];
}
