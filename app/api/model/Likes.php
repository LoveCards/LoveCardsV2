<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

use app\api\model\Cards as CardsModel;

class Likes extends Model
{

    // 设置当前模型对应的完整数据表名称
    protected $table = 'good';

    //自动时间戳
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';

    // 设置字段信息
    protected $schema = [
        'id' => 'INT',
        'aid' => 'INT',
        'pid' => 'INT',
        'uid' => 'INT',
        'ip' => 'VARCHAR',
        'created_at' => 'TIMESTAMP',
    ];

    // 默认排除字段
    // protected static $withoutField = [
    //     'created_at',
    // ];

    public function card()
    {
        return $this->hasOne(CardsModel::class, 'id', 'pid');
    }
}
