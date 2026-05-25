<?php

namespace app\api\validate;

use think\Validate;

class Files extends Validate
{
    protected $rule = [
        'scene'     => 'in:card,comment,avatar,direct',
        'ref_type'  => 'max:32',
        'ref_id'    => 'integer',
        'is_public' => 'in:0,1',
        'method'    => 'in:approve,ban,toggle_public,trash,restore,hard_delete',
    ];
}
