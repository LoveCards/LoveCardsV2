<?php

namespace app\api\controller\user;

use think\facade\Request;
use think\facade\Db;

use app\api\validate\Cards as CardsValidate;
use app\api\validate\Comments as CommentsValidate;

use app\api\service\Cards as CardsService;
use app\api\service\Likes as LikesService;
use app\api\service\Comments as CommentsService;

use app\api\model\Likes as LikesModel; //需要优化

use app\api\controller\BaseController;
use app\api\controller\Params;

use app\api\controller\ApiResponse;

class Cards extends BaseController
{
    var $Params;

    public function __construct()
    {
        parent::__construct();
        $this->Params = new Params();
    }

    public function list(CardsService $CardsService)
    {

        //获取过滤参数
        $params = $this->Params->IndexParams(Request::param());
        //调用服务
        $result = $CardsService->newList($params, $this->JWT_SESSION['uid']);
        //返回结果
        return ApiResponse::createSuccess($result['data']);
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
        $params['user_id'] = $this->JWT_SESSION['uid'];
        $params['post_ip'] = $this->SESSION['ip'];
        $params['status'] = $this->SYSTEM_CONFIG['Cards']['Approve'] ? 3 : 0;

        //调用服务
        $result = CardsService::createCard($params);
        //返回结果
        if ($this->SYSTEM_CONFIG['Cards']['Approve']) {
            return ApiResponse::createCreated();
        }
        return ApiResponse::createSuccess($result['data']);
    }
    //隐藏卡片(用户删除)
    public function hideCard()
    {
        try {
            //隐藏
            CardsService::updata(['status' => 2], ['id' => Request::param('id'), 'user_id' => $this->JWT_SESSION['uid']], ['status']);
        } catch (\Throwable $th) {
            return ApiResponse::createError($th->getMessage());
        }
        return ApiResponse::createNoCntent([]);
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
        $params['user_id'] = $this->JWT_SESSION['uid'];
        $params['post_ip'] = $this->SESSION['ip'];
        $params['status'] = $this->SYSTEM_CONFIG['Comments']['Approve'] ? 3 : 0;

        //调用服务
        $result = CommentsService::createComment($params);

        //返回结果
        if ($this->SYSTEM_CONFIG['Comments']['Approve']) {
            return ApiResponse::createCreated();
        }
        return ApiResponse::createSuccess($result['data']);
    }
    //隐藏评论(用户删除)
    public function hideComment() {}

    //点赞
    public function like()
    {
        $context = request()->JwtData;

        //获取数据
        $id = Request::param('id');
        $ip = $this->SESSION['ip'];
        //$time = $this->SESSION['date'];

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
        if (!$resultCards->inc('good')->update()) {
            return ApiResponse::createBadRequest('点赞失败', ['cards.good更新失败']);
        };

        $data = [
            'aid' => '1',
            'pid' => $id,
            'uid' => $context['uid'],
            'ip' => $ip,
            // 'time' => $time
        ];
        if (!$resultGood->save($data)) {
            return ApiResponse::createBadRequest('点赞失败', ['good写入失败']);
        };

        //返回数据
        return ApiResponse::createSuccess([$resultCardsData['good'] + 1]);
    }
}
