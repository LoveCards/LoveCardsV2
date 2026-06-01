<?php

namespace app\api\controller\Content;

use think\facade\Request;

use app\api\validate\Comments as CommentsValidate;
use app\api\service\Content\Comments as CommentsService;
use app\common\service\Config as ConfigService;

use app\api\ApiResponse;
use app\api\controller\BaseController;
use app\api\controller\BatchOperateTrait;

class Comments extends BaseController
{
    use BatchOperateTrait;

    protected function getBatchService(): string
    {
        return \app\api\service\Content\Comments::class;
    }

    public function cardList($id)
    {
        $params = $this->paramIndex(Request::param());
        $params['where'] = ['aid' => 1, 'pid' => $id];
        $result = CommentsService::listAll($params);
        return ApiResponse::createOk($result);
    }

    public function create($id)
    {
        $params = $this->param(CommentsValidate::class, CommentsValidate::$all_scene['create'], Request::param());
        $params['id'] = $id;
        $params['user_id'] = request()->uid ?? 0;
        $params['post_ip'] = request()->ip();
        $params['status'] = ConfigService::get('comments.approve') ? 3 : 0;

        $result = CommentsService::createComment($params);

        if (ConfigService::get('comments.approve')) {
            return ApiResponse::createCreated();
        }
        return ApiResponse::createOk($result['data']);
    }

    public function get($id)
    {
        $result = CommentsService::get((int) $id);
        return ApiResponse::createOk($result);
    }

    public function update($id)
    {
        $params = $this->param(CommentsValidate::class, CommentsValidate::$all_scene['create'], Request::param());
        $params['id'] = (int) $id;

        CommentsService::updateComment($params, request()->uid ?? 0, request()->caps ?? []);

        return ApiResponse::createNoContent();
    }

    public function delete($id)
    {
        CommentsService::deleteComments([(int) $id], request()->uid ?? 0, request()->caps ?? []);
        return ApiResponse::createNoContent();
    }

    public function listOwn()
    {
        $params = $this->paramIndex(Request::param());
        $result = CommentsService::newList($params, request()->uid ?? 0);
        return ApiResponse::createOk($result);
    }
}
