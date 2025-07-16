<?php

namespace app\api\controller\user;

use think\facade\Request;
use think\facade\Db;

use app\api\validate\Cards as CardsValidate;

use app\api\service\Cards as CardsService;
use app\api\service\Likes as LikesService;

use app\common\Export;

use app\api\controller\Base;

class Cards extends Base
{
    //卡片
    public function list() {}

    //创建卡片
    public function createCard()
    {
        //获取参数
        $params = $this->getParams(CardsValidate::class, CardsValidate::$all_scene['user']['create']);
        if (gettype($params) == 'object') {
            return $params;
        }

        //补齐参数
        $params['user_id'] = $this->JWT_SESSION['uid'];
        $params['post_ip'] = $this->SESSION['ip'];

        //调用服务
        $result = CardsService::createCard($params);
        //返回结果
        return Export::Create($result['data'], 200, null);
    }
    //隐藏卡片(用户删除)
    public function hideCard()
    {
        try {
            //隐藏
            CardsService::updata(['status' => 3], ['id' => Request::param('id')]);
        } catch (\Throwable $th) {
            return Export::Create([], $th->getCode(), $th->getMessage());
        }
        return Export::Create([]);
    }

    //创建评论
    public function creatComment() {}
    //隐藏评论(用户删除)
    public function hideComment() {}

    //点赞
    public function like()
    {
        $context = request()->JwtData;

        //获取数据
        $id = Request::param('id');
        $ip = $this->SESSION['ip'];
        $time = $this->SESSION['date'];

        //获取Cards数据库对象
        $resultCards = Db::table('cards')->where('id', $id);
        $resultCardsData = $resultCards->find();
        if (!$resultCardsData) {
            return Export::Create(null, 400, 'id不存在');
        }

        //获取good数据库对象
        $resultGood = Db::table('good');
        if ($resultGood->where('pid', $id)->where('ip', $ip)->find()) {
            return Export::Create(['请勿重复点赞'], 400, '点赞失败');
        }

        //更新视图字段
        if (!$resultCards->inc('good')->update()) {
            return Export::Create(['cards.good更新失败'], 400, '点赞失败');
        };

        $data = [
            'aid' => '1',
            'pid' => $id,
            'uid' => $context['uid'],
            'ip' => $ip,
            'time' => $time
        ];
        if (!$resultGood->insert($data)) {
            return Export::Create(['good写入失败'], 400, '点赞失败');
        };

        //返回数据
        return Export::Create([$resultCardsData['good'] + 1], 200, null);
    }
    //取消点赞
    public function unLike()
    {
        // try {
        //     //隐藏
        //     LikesService::delete($data, $context);
        // } catch (\Throwable $th) {
        //     return Export::Create([], $th->getCode(), $th->getMessage());
        // }
        // return Export::Create([]);
    }
}
