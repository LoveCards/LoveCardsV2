<?php

namespace app\api\controller\user;

use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Config;

use app\api\model\Images as ImagesModel;

use app\api\service\Likes as LikesService;

use app\common\Common;
use app\common\Export;
use app\common\BackEnd;
use app\common\App;

class Likes extends Common
{

    //列表
    public function List()
    {}
}
