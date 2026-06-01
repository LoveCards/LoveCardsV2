<?php

namespace app\api\model;

use think\Model;

class RoleCapabilities extends Model
{
    protected $autoWriteTimestamp = false;

    protected $schema = [
        'id'         => 'int',
        'role_id'    => 'int',
        'capability' => 'string',
    ];
}
