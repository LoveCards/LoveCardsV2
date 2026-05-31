<?php

namespace app\api\controller\Sender;

use think\facade\Request;

use app\api\service\Sender\SenderManager;
use app\api\ApiException;
use app\api\ApiResponse;
use app\api\controller\BaseController;

class Sender extends BaseController
{
    public function meta(string $type)
    {
        return ApiResponse::createOk(SenderManager::meta($type));
    }

    public function install()
    {
        return ApiResponse::createOk(SenderManager::install());
    }

    public function types()
    {
        return ApiResponse::createOk(SenderManager::types());
    }

    public function channels()
    {
        return ApiResponse::createOk(SenderManager::channels());
    }

    public function templates()
    {
        return ApiResponse::createOk(SenderManager::templates());
    }

    public function testChannel()
    {
        $channel = Request::param('channel', '');
        if (empty($channel)) {
            throw ApiException::badRequest('请指定渠道');
        }

        $to = Request::param('to', 'test@example.com');

        return ApiResponse::createOk(SenderManager::testChannel($channel, $to));
    }
}
