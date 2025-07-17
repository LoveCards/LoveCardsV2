<?php

namespace app\api\controller\user;

use app\api\service\Comments as CommentsService;
use think\facade\Db;
use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Config;

use app\api\validate\Comments as CommentsValidate;
use app\common\App;
use app\common\Common;
use app\common\Export;

use app\api\controller\Base;

class Comments extends Base
{
    //列表
    public function List() {}
}
