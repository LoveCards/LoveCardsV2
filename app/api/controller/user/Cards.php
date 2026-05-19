<?php

namespace app\api\controller\user;

use think\facade\Request;
use think\facade\Db;
use think\facade\Config;

use app\api\validate\Cards as CardsValidate;
use app\api\validate\Comments as CommentsValidate;

use app\api\model\Cards as CardsModel;

use app\api\service\Content\Cards as CardsService;
use app\api\service\Content\Likes as LikesService;
use app\api\service\Content\Comments as CommentsService;
use app\api\service\Config as ConfigService;

use app\api\model\Likes as LikesModel; //需要优化

use app\api\controller\BaseController;
use app\api\Params;

use app\api\ApiResponse;

class Cards extends BaseController
{
    var $Params;

    public function __construct()
    {
        parent::__construct();
        $this->Params = new Params();
    }

    //我的卡片列表
    public function list()
    {
        //获取过滤参数
        $params = $this->Params->IndexParams(Request::param());
        //调用服务
        $result = CardsService::list($params, request()->uid);
        //返回结果
        return ApiResponse::createOk($result);
    }

    //创建卡片
    public function createCard()
    {
        //获取参数
        $params = $this->Params->getParams(CardsValidate::class, CardsValidate::$all_scene['user']['create'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        //补齐参数
        $params['user_id'] = request()->uid;
        $params['post_ip'] = request()->ip();
        $params['status'] = ConfigService::get('cards.approve') ? 3 : 0;

        //调用服务
        $cardId = CardsService::createCard($params);
        //返回结果
        if (ConfigService::get('cards.approve')) {
            return ApiResponse::createCreated();
        }
        return ApiResponse::createOk(['id' => $cardId]);
    }
    //隐藏卡片(用户删除)
    public function hideCard()
    {
        $result = CardsModel::where([
            'id' => Request::param('id'),
            'user_id' => request()->uid,
            'status' => 0
        ])->update(['status' => 2]);

        if ($result === 0) {
            return ApiResponse::createNotFound([]);
        }
        return ApiResponse::createNoContent([]);
    }

    //创建评论
    public function createComment()
    {
        //获取参数
        $params = $this->Params->getParams(CommentsValidate::class, CommentsValidate::$all_scene['user']['create'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        //补齐参数
        $params['user_id'] = request()->uid;
        $params['post_ip'] = request()->ip();
        $params['status'] = ConfigService::get('comments.approve') ? 3 : 0;

        //调用服务
        $result = CommentsService::createComment($params);

        //返回结果
        if (ConfigService::get('comments.approve')) {
            return ApiResponse::createCreated();
        }
        return ApiResponse::createOk($result['data']);
    }
    //隐藏评论(用户删除)
    public function hideComment() {}

    //点赞
    public function like()
    {
        //获取数据
        $id = Request::param('id');
        $ip = request()->ip();

        //获取Cards数据库对象
        $resultCards = Db::table('cards')->where('id', $id);
        $resultCardsData = $resultCards->find();
        if (!$resultCardsData) {
            return ApiResponse::createBadRequest('id不存在');
        }

        //获取good数据库对象
        $resultGood = new LikesModel();
        if ($resultGood->where('pid', $id)->where('ip', $ip)->find()) {
            return ApiResponse::createBadRequest('点赞失败', ['请勿重复点赞']);
        }

        //更新视图字段
        if (!$resultCards->inc('goods')->update()) {
            return ApiResponse::createBadRequest('点赞失败', ['cards.goods更新失败']);
        };

        $data = [
            'aid' => '1',
            'pid' => $id,
            'uid' => request()->uid,
            'ip' => $ip,
            // 'time' => $time
        ];
        if (!$resultGood->save($data)) {
            return ApiResponse::createBadRequest('点赞失败', ['good写入失败']);
        };

        //返回数据
        return ApiResponse::createOk([$resultCardsData['goods'] + 1]);
    }
}
