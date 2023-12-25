<?php

namespace app\api\validate;

use think\Validate;
use think\facade\Config;

use app\common\Base as CommonBase;

class Upload extends Validate
{

    public function __construct()
    {
        // 确保执行父类构造
        parent::__construct();

        // 动态设置文件大小和扩展名验证规则
        $lDef_ImageSize = CommonBase::conf()->mArraySearchConfigKey('UserImageSize');
        $lDef_ImageSize[0] != '' ? $lDef_ImageSize = $lDef_ImageSize[0] : $lDef_ImageSize = 2;
        $lDef_ImageExt = CommonBase::conf()->mArraySearchConfigKey('UserImageExt');
        $lDef_ImageExt[0] != '' ? $lDef_ImageExt = $lDef_ImageExt[0] : $lDef_ImageExt = 'jpg,png,gif';
        $this->rule['file'] = $this->rule['file'] . '|fileSize:' . (1024 * 1000 * $lDef_ImageSize) . '|fileExt:' . $lDef_ImageExt;
    }

    //定义验证规则
    protected $rule =   [
        'aid' => 'require|number',
        'pid' => 'require|number',
        'uid' => 'require|number',
        'url' => 'require',
        'file' => 'require',
    ];

    //定义错误信息
    protected $message  =   [
        'aid.require' => 'aid不得为空',
        'aid.number' => 'aid格式错误',

        'pid.require' => 'pid不得为空',
        'pid.number' => 'pid格式错误',

        'uid.require' => 'uid不得为空',
        'uid.number' => 'uid格式错误',

        'url.require' => 'url不得为空',
        //'url.number' => 'url格式错误',

        'file.require' => '图片不得为空',
        'file.fileSize' => '图片超出上传限制',
        'file.fileExt' => '图片格式错误',
    ];

    // edit 验证场景定义
    public function sceneCheckImage()
    {
        return $this->only(['file']);
    }

    public function sceneCheckUpload()
    {
        return $this->only(['aid', 'uid', 'pid', 'file'])->remove('uid', 'require');
    }
}
