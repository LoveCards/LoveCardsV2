<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

//use app\common\Common;
use app\api\model\Tags as TagsModel;

class TagsMap extends Model
{
    //自动时间戳
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';

    // 设置字段信息
    protected $schema = [
        'id' => 'int',
        'aid' => 'int',
        'pid' => 'int',
        'tid' => 'int',
        'status' => 'int',
        'created_at' => 'datetime',
    ];

    // 默认排除字段
    // protected static $withoutField = [
    //     'created_at'
    // ];
}
