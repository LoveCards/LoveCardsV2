<?php
/*
 * @Description: 
 * @Author: github.com/zhiguai
 * @Date: 2025-08-15 16:22:13
 * @Email: 2903074366@qq.com
 */

namespace app\api\controller\user;

use think\facade\Request;
use think\exception\ValidateException;

use app\api\service\Users as UsersService;
use app\api\validate\Users as UsersValidate;

use app\common\Common;

use captcha\Code;
use email\Email;

use app\api\controller\ApiResponse;

class Info
{
    /**
     * UsersServicePatch方法封装
     * 更新并返回结果 -可以移入UsersService
     * @param Array $lDef_ParamData
     * @return Object
     */
    public function mObjectEasyUsersServicePatch($lDef_ParamData)
    {
        $tDef_Result = UsersService::Patch($lDef_ParamData['id'], array_diff($lDef_ParamData, [null, '']));
        if ($tDef_Result['status']) {
            return ApiResponse::createNoCntent();
        }

        //错误返回
        $lDef_ErrorMsg = $tDef_Result['data']->getMessage();
        return ApiResponse::createError($lDef_ErrorMsg);
    }

    /**
     * CodeCreateCaptcha方法封装
     * 验证并发送验证码
     * @param String $account
     * @param String $key
     * @param Array $errorDetail
     * @param Integer $time
     * @return Object
     */
    public function mObjectEasyCodeCreateCaptcha($account = '', $key = '', $errorDetail = ['目前仅支持邮箱验证'], $time = 300)
    {
        //判断是手机号还是邮箱
        if (Common::mBoolEasyIsPhoneNumberOrEmail($account) == 'email') {
            //获取验证码
            $data = Code::CreateCaptcha($account, $key, $time);
            $code = $data['data'];
            //发送邮件
            try {
                $result = Email::SendCaptcha($code, $account);
            } catch (\Exception $e) {
                return ApiResponse::createError('发送失败', ['邮件模块发生错误']);
            }
            if ($result['status']) {
                return ApiResponse::createSuccess([$time . 's后失效']);
            } else {
                return ApiResponse::createError('发送失败', [$result['msg']]);
            }
        } else {
            return ApiResponse::createError('发送失败', $errorDetail);
        }
    }

    /**
     * TryCatchUsersValidate方法封装
     * 验证并返回结果通过则不返回
     * @param Array $lDef_ParamData
     * @return Object
     */
    public function mObjectEasyTryCatchUsersValidate($lDef_ParamData = [])
    {
        //验证修改参数是否合法
        try {
            validate(UsersValidate::class)
                ->batch(true)
                ->scene('edit')
                ->check($lDef_ParamData);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $uservalidateerror = $e->getError();
            return ApiResponse::createBadRequest('编辑失败', $uservalidateerror);
        }
    }

    //获取资料-GET
    public function Get()
    {
        $context = request()->JwtData;

        $tDef_Result = UsersService::Get($context['uid'], ['id']);

        if ($tDef_Result['status']) {
            return ApiResponse::createSuccess($tDef_Result['data']);
        }

        return ApiResponse::createError($tDef_Result['msg']);
    }

    //编辑资料-Patch
    public function Patch()
    {
        $context = request()->JwtData;

        //传入必要参数
        $lDef_ParamData = [
            'id' => $context['uid'],
            'avatar' => Request::param('avatar'),
            'username' => Request::param('username'),
            'password' => Request::param('password'),
        ];

        $this->mObjectEasyTryCatchUsersValidate($lDef_ParamData);

        //如果密码存在则进行密码加密
        if ($lDef_ParamData['password']) {
            $lDef_ParamData['password'] = password_hash($lDef_ParamData['password'], PASSWORD_DEFAULT);
        }

        return $this->mObjectEasyUsersServicePatch($lDef_ParamData);
    }

    //修改密码-Post
    public function PostPassword()
    {
        $context = request()->JwtData;

        //传入必要参数
        $lDef_ParamData = [
            'id' => $context['uid'],
            'password' => Request::param('password'),
        ];

        if (!$lDef_ParamData['password']) {
            return ApiResponse::createBadRequest('编辑失败', ['密码不可为空']);
        }

        $this->mObjectEasyTryCatchUsersValidate($lDef_ParamData);

        $lDef_ParamData['password'] = password_hash($lDef_ParamData['password'], PASSWORD_DEFAULT);

        return $this->mObjectEasyUsersServicePatch($lDef_ParamData);
    }

    //修改邮箱-Post
    public function PostEmail()
    {
        $context = request()->JwtData;

        $lDef_ParamData = [
            'id' => $context['uid'],
            'email' => Request::param('email')
        ];

        //验证码与邮箱校验
        if (!Code::CheckCaptcha($lDef_ParamData['email'], strtoupper(Request::param('captcha')), 'Info_BindEmailCaptcha')) {
            return ApiResponse::createBadRequest('编辑失败', ['验证码错误']);
        };

        //验证邮箱格式
        $this->mObjectEasyTryCatchUsersValidate($lDef_ParamData);

        return $this->mObjectEasyUsersServicePatch($lDef_ParamData);
    }

    //获取邮箱绑定验证码-POST
    public function PostBindEmailCaptcha()
    {
        $account = Request::param('email');

        //验证并发送验证码
        return $this->mObjectEasyCodeCreateCaptcha($account, 'Info_BindEmailCaptcha', ['邮箱格式错误']);
    }

    //获取身份校验验证码-POST
    // public function PostAuthCaptcha()
    // {
    //     $context = request()->JwtData;
    //     $tDef_Result = UsersService::Get($context['uid'], ['id']);
    //     //优先邮箱
    //     $tDef_Result['email'] ? $account = $tDef_Result['email'] : $account = $tDef_Result['phone'];

    //     //验证并发送验证码
    //     return $this->mObjectEasyCodeCreateCaptcha($account, 'Info_AuthCaptcha');
    // }


    //电话绑定验证码-POST
    // public function PostBindPhoneCaptcha()
    // {
    //     return Export::Create(['目前仅支持邮箱验证'], 500, '发送失败');
    // }
}
