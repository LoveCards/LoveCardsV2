<?php

namespace app\api\controller\Captcha;

use app\api\service\Captcha\CaptchaManager;
use app\api\ApiResponse;
use app\api\controller\BaseController;

class Captcha extends BaseController
{
    public function types()
    {
        return ApiResponse::createOk(CaptchaManager::types());
    }

    public function drivers()
    {
        return ApiResponse::createOk(CaptchaManager::drivers());
    }

    public function meta(string $slug)
    {
        return ApiResponse::createOk(CaptchaManager::meta($slug));
    }

    public function install()
    {
        return ApiResponse::createOk(CaptchaManager::install());
    }

    public function config()
    {
        return ApiResponse::createOk(CaptchaManager::config());
    }
}
