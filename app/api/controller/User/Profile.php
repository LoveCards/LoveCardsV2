<?php

namespace app\api\controller\User;

use think\facade\Request;
use think\exception\ValidateException;

use app\api\service\User\Profile as ProfileService;
use app\api\validate\Users as UsersValidate;

use app\api\ApiResponse;
use app\api\controller\BaseController;

class Profile extends BaseController
{
    public function get()
    {
        $userData = ProfileService::get(request()->uid);
        return ApiResponse::createOk($userData);
    }

    public function update()
    {
        $params = [];
        if (Request::has('avatar')) $params['avatar'] = Request::param('avatar');
        if (Request::has('username')) $params['username'] = Request::param('username');
        if (Request::has('password')) $params['password'] = Request::param('password');

        if (empty($params)) {
            return ApiResponse::createNoContent();
        }

        // 添加 id 用于验证，然后移除
        $params['id'] = request()->uid;
        $params = $this->validateAndClean($params, '编辑失败');
        unset($params['id']);

        ProfileService::update(request()->uid, $params);
        return ApiResponse::createNoContent();
    }

    public function password()
    {
        $password = Request::param('password', '');

        try {
            ProfileService::changePassword(request()->uid, $password);
        } catch (\app\api\ApiException $e) {
            return ApiResponse::createBadRequest('编辑失败', [$e->getMessage()]);
        }

        return ApiResponse::createNoContent();
    }

    public function email()
    {
        $email = Request::param('email');
        $captcha = Request::param('captcha', '');

        try {
            ProfileService::changeEmail(request()->uid, $email, $captcha);
        } catch (\app\api\ApiException $e) {
            return ApiResponse::createBadRequest('编辑失败', [$e->getMessage()]);
        }

        return ApiResponse::createNoContent();
    }

    public function emailCaptcha()
    {
        $email = Request::param('email');

        try {
            ProfileService::sendEmailCaptcha($email);
        } catch (\RuntimeException $e) {
            return ApiResponse::createBadRequest($e->getMessage());
        } catch (\app\api\ApiException $e) {
            return ApiResponse::createBadRequest($e->getMessage());
        }

        return ApiResponse::createOk(['300s后失效']);
    }

    private function validateAndClean(array $params, string $errorPrefix): array
    {
        try {
            validate(UsersValidate::class)
                ->batch(true)
                ->scene('edit')
                ->check($params);
        } catch (ValidateException $e) {
            throw \app\api\ApiException::badRequest($errorPrefix, \app\api\ApiException::CODE_PARAM_INVALID, $e->getError());
        }

        return $params;
    }
}
