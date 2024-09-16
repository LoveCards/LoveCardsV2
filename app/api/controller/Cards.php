<?php

namespace app\api\controller;

use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Config;

use app\api\validate\Cards as CardsValidate;
use app\api\validate\CardsSetting as CardsValidateSetting;

use app\api\model\Images as ImagesModel;

use app\api\service\Cards as CardsService;

use app\common\Common;
use app\common\Export;
use app\common\BackEnd;
use app\common\App;

class Cards extends Common
{
    //在此处设计多权限多态

    //列表
    public function List()
    {
        try {
            $result = CardsService::list();
        } catch (\Throwable $th) {
            return Export::Create([], $th->getCode(), $th->getMessage());
        }
        return Export::Create($result);
    }

    //删除
    public function Delete()
    {
        try {
            CardsService::updata(['status' => 1], ['id' => Request::param('id')]);
        } catch (\Throwable $th) {
            return Export::Create([], $th->getCode(), $th->getMessage());
        }
        return Export::Create([]);
    }
}
