<?php

namespace app\api\model;

use think\Model;

class Likes extends Model
{
    protected $table = 'likes';

    protected $autoWriteTimestamp = 'timestamp';
    protected $createTime = 'created_at';
    protected $updateTime = false;

    protected $schema = [
        'id' => 'int',
        'aid' => 'int',       // legacy: app id
        'pid' => 'int',       // legacy: content id
        'ref_type' => 'string', // new: content type (card, comment)
        'ref_id' => 'int',      // new: content id
        'uid' => 'int',
        'ip' => 'varchar',
        'created_at' => 'timestamp',
    ];
}
