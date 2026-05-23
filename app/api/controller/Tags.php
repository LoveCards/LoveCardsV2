<?php

namespace app\api\controller;

use think\facade\Request;

use app\api\service\Content\Tags as TagsService;
use app\api\validate\Tags as TagsValidate;

use app\api\ApiResponse;

class Tags extends BaseController
{
    use BatchOperateTrait;

    protected function getBatchService(): string
    {
        return \app\api\service\Content\Tags::class;
    }

    public function list()
    {
        $params = [
            'where' => ['status' => 0]
        ];
        $result = TagsService::noPaginateIndex($params);
        return ApiResponse::createOk($result);
    }

    public function get($id)
    {
        $result = TagsService::get((int) $id);
        return ApiResponse::createOk($result);
    }

    public function create()
    {
        $params = $this->param(TagsValidate::class, TagsValidate::$all_scene['create'], Request::param());
        $params['user_id'] = request()->uid;
        TagsService::createTag($params);
        return ApiResponse::createOk();
    }

    public function update($id)
    {
        $params = $this->param(TagsValidate::class, TagsValidate::$all_scene['allUpdate'], Request::param());
        $params['id'] = (int) $id;
        TagsService::updateTag($params);
        return ApiResponse::createOk();
    }

    public function delete($id)
    {
        TagsService::deleteTags((int) $id);
        return ApiResponse::createNoContent();
    }

    public function allList()
    {
        $params = $this->paramIndex(Request::param());
        $result = TagsService::listAll($params);
        return ApiResponse::createOk($result);
    }

    public function allCreate()
    {
        $params = $this->param(TagsValidate::class, TagsValidate::$all_scene['allCreate'], Request::param());
        $params['user_id'] = request()->uid;
        TagsService::allCreate($params);
        return ApiResponse::createOk();
    }

    public function allUpdate($id)
    {
        $params = $this->param(TagsValidate::class, TagsValidate::$all_scene['allUpdate'], Request::param());
        $params['id'] = (int) $id;
        TagsService::updateAny($params);
        return ApiResponse::createOk();
    }

    public function allDelete($id)
    {
        TagsService::deleteAny((int) $id);
        return ApiResponse::createNoContent();
    }
}
