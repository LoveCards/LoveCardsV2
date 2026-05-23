<?php

namespace app\api\controller;

use think\facade\Request;

use app\api\validate\Cards as CardsValidate;
use app\api\validate\Comments as CommentsValidate;

use app\api\service\Content\Cards as CardsService;
use app\api\service\Content\Likes as LikesService;
use app\api\service\Content\Comments as CommentsService;
use app\api\service\Config as ConfigService;

use app\api\ApiResponse;

class Cards extends BaseController
{
    use BatchOperateTrait;

    protected function getBatchService(): string
    {
        return \app\api\service\Content\Cards::class;
    }

    public function list()
    {
        $params = $this->paramIndex(Request::param());
        $result = CardsService::list($params);
        return ApiResponse::createOk($result);
    }

    public function hotList()
    {
        $result = CardsService::hotList();
        return ApiResponse::createOk($result);
    }

    public function get($id)
    {
        $result = CardsService::get((int) $id);
        return ApiResponse::createOk($result);
    }

    public function create()
    {
        $params = $this->param(CardsValidate::class, CardsValidate::$all_scene['create'], Request::param());

        $params['user_id'] = request()->uid;
        $params['post_ip'] = request()->ip();
        $params['status'] = ConfigService::get('cards.approve') ? 3 : 0;

        $cardId = CardsService::createCard($params);

        if (ConfigService::get('cards.approve')) {
            return ApiResponse::createCreated();
        }
        return ApiResponse::createOk(['id' => $cardId]);
    }

    public function update($id)
    {
        $params = $this->param(CardsValidate::class, CardsValidate::$all_scene['create'], Request::param());
        $params['id'] = (int) $id;
        $params['user_id'] = request()->uid;

        CardsService::updateCard($params);
        return ApiResponse::createNoContent();
    }

    public function delete($id)
    {
        CardsService::deleteCards((int) $id);
        return ApiResponse::createNoContent([]);
    }

    public function like($id)
    {
        $likes = LikesService::like('card', (int) $id, request()->uid, request()->ip());
        return ApiResponse::createOk(['likes' => $likes]);
    }

    public function comment($id)
    {
        $params = $this->param(CommentsValidate::class, CommentsValidate::$all_scene['create'], Request::param());
        $params['id'] = $id;

        $params['user_id'] = request()->uid;
        $params['post_ip'] = request()->ip();
        $params['status'] = ConfigService::get('comments.approve') ? 3 : 0;

        $result = CommentsService::createComment($params);

        if (ConfigService::get('comments.approve')) {
            return ApiResponse::createCreated();
        }
        return ApiResponse::createOk($result['data']);
    }

    public function listOwn()
    {
        $params = $this->paramIndex(Request::param());
        $result = CardsService::listOwn($params, request()->uid);
        return ApiResponse::createOk($result);
    }

    public function allList()
    {
        $params = $this->paramIndex(Request::param());
        $result = CardsService::listAll($params);
        return ApiResponse::createOk($result);
    }

    public function allGet($id)
    {
        $result = CardsService::getAny((int) $id);
        return ApiResponse::createOk($result);
    }

    public function allUpdate($id)
    {
        $params = $this->param(CardsValidate::class, CardsValidate::$all_scene['allUpdate'], Request::param());
        $params['id'] = (int) $id;

        CardsService::updateAny($params);
        return ApiResponse::createNoContent();
    }

    public function allDelete($id)
    {
        CardsService::deleteAny((int) $id);
        return ApiResponse::createNoContent();
    }
}
