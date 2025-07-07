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
    protected $autoWriteTimestamp = 'timestamp';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    // 设置字段信息
    protected $schema = [
        'id' => 'INT',
        'aid' => 'INT',
        'user_id' => 'INT',
        'name' => 'VARCHAR',
        'status' => 'INT',
        'created_at' => 'TIMESTAMP',
        'updated_at' => 'TIMESTAMP',
        'deleted_at' => 'TIMESTAMP',
    ];
}
