<?php

namespace app\api\controller\user;

use think\facade\Request;
use think\exception\ValidateException;

use app\api\model\Users as UsersModel;
use app\api\validate\Users as UsersValidate;

use app\common\Common;
use app\common\Export;

use captcha\Code;
use email\Email;

class Info
{
    /**
     * UsersModelPatch方法封装
     * 更新并返回结果 -可以移入UsersModel
     * @param Array $lDef_ParamData
     * @return Object
     */
    public function mObjectEasyUsersModelPatch($lDef_ParamData)
    {
        $tDef_Result = UsersModel::Patch($lDef_ParamData['id'], array_diff($lDef_ParamData, [null, '']));
        if ($tDef_Result['status']) {
            return Export::Create(null, 200, null);
        }

        //错误返回
        $lDef_ErrorMsg = $tDef_Result['data']->getMessage();
        return Export::Create(null, 500, $lDef_ErrorMsg);
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
                return Export::Create(['邮件模块发生错误'], 500, '发送失败');
            }
            if ($result['status']) {
                return Export::Create([$time . 's后失效'], 200);
            } else {
                return Export::Create([$result['msg']], 500, '发送失败');
            }
        } else {
            return Export::Create($errorDetail, 500, '发送失败');
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
            return Export::Create($uservalidateerror, 400, '编辑失败');
        }
    }

    //获取资料-GET
    public function Get()
    {
        $context = request()->JwtData;

        $tDef_Result = UsersModel::Get($context['uid'], ['id']);

        if ($tDef_Result['status']) {
            return Export::Create($tDef_Result['data'], 200, null);
        }

        return Export::Create(null, 500, $tDef_Result['msg']);
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

        return $this->mObjectEasyUsersModelPatch($lDef_ParamData);
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
            return Export::Create(['密码不可为空'], 400, '编辑失败');
        }

        $this->mObjectEasyTryCatchUsersValidate($lDef_ParamData);

        $lDef_ParamData['password'] = password_hash($lDef_ParamData['password'], PASSWORD_DEFAULT);

        return $this->mObjectEasyUsersModelPatch($lDef_ParamData);
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
            return Export::Create(['验证码错误'], 400, '编辑失败');
        };

        //验证邮箱格式
        $this->mObjectEasyTryCatchUsersValidate($lDef_ParamData);

        return $this->mObjectEasyUsersModelPatch($lDef_ParamData);
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
    //     $tDef_Result = UsersModel::Get($context['uid'], ['id']);
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
