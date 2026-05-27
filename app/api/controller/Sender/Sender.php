<?php

namespace app\api\controller\Sender;

use think\facade\Request;

use app\api\service\Sender\SenderManager;
use app\api\ApiResponse;
use app\api\controller\BaseController;

class Sender extends BaseController
{
    public function meta(string $type)
    {
        try {
            return ApiResponse::createOk(SenderManager::meta($type));
        } catch (\Throwable $e) {
            return ApiResponse::createBadRequest($e->getMessage());
        }
    }

    public function install()
    {
        try {
            return ApiResponse::createOk(SenderManager::install());
        } catch (\Throwable $e) {
            return ApiResponse::createBadRequest($e->getMessage());
        }
    }

    public function types()
    {
        try {
            return ApiResponse::createOk(SenderManager::types());
        } catch (\Throwable $e) {
            return ApiResponse::createBadRequest($e->getMessage());
        }
    }

    public function channels()
    {
        try {
            return ApiResponse::createOk(SenderManager::channels());
        } catch (\Throwable $e) {
            return ApiResponse::createBadRequest($e->getMessage());
        }
    }

    public function templates()
    {
        try {
            return ApiResponse::createOk(SenderManager::templates());
        } catch (\Throwable $e) {
            return ApiResponse::createBadRequest($e->getMessage());
        }
    }

    public function testChannel()
    {
        try {
            $channel = Request::param('channel', '');
            if (empty($channel)) {
                return ApiResponse::createBadRequest('请指定渠道');
            }

            $to = Request::param('to', 'test@example.com');

            return ApiResponse::createOk(SenderManager::testChannel($channel, $to));
        } catch (\Throwable $e) {
            return ApiResponse::createBadRequest($e->getMessage());
        }
    }
}
