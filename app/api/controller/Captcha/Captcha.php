<?php

namespace app\api\controller\Captcha;

use think\facade\Request;

use app\api\service\Captcha\CaptchaManager;
use app\api\ApiResponse;
use app\api\controller\BaseController;

class Captcha extends BaseController
{
    public function types()
    {
        try {
            return ApiResponse::createOk(CaptchaManager::types());
        } catch (\Throwable $e) {
            return ApiResponse::createBadRequest($e->getMessage());
        }
    }

    public function drivers()
    {
        try {
            return ApiResponse::createOk(CaptchaManager::drivers());
        } catch (\Throwable $e) {
            return ApiResponse::createBadRequest($e->getMessage());
        }
    }

    public function meta(string $slug)
    {
        try {
            return ApiResponse::createOk(CaptchaManager::meta($slug));
        } catch (\Throwable $e) {
            return ApiResponse::createBadRequest($e->getMessage());
        }
    }

    public function install()
    {
        try {
            return ApiResponse::createOk(CaptchaManager::install());
        } catch (\Throwable $e) {
            return ApiResponse::createBadRequest($e->getMessage());
        }
    }

    public function config()
    {
        try {
            return ApiResponse::createOk(CaptchaManager::config());
        } catch (\Throwable $e) {
            return ApiResponse::createBadRequest($e->getMessage());
        }
    }
}
