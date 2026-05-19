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
use app\api\service\RBAC\RBAC;
use app\api\model\Roles as RolesModel;
use app\api\validate\Users as UsersValidate;

use app\common\captcha\Code;
use app\common\email\Email;

use app\api\ApiResponse;

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
        UsersService::Patch($lDef_ParamData['id'], array_diff($lDef_ParamData, [null, '']));

        return ApiResponse::createNoContent();
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
        if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
            $data = Code::CreateCaptcha($account, $key, $time);
            $code = $data['data'];
            Email::SendCaptcha($code, $account);
            return ApiResponse::createOk([$time . 's后失效']);
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
        $user = UsersService::Get(request()->uid, ['id']);
        $userData = $user->toArray();

        $rolesId = request()->rolesId ?? (json_decode($user->roles_id, true) ?: []);
        $userData['roles'] = RolesModel::whereIn('id', $rolesId)
            ->whereNull('deleted_at')
            ->field('id,name,slug')
            ->select()
            ->toArray();
        $userData['permissions'] = RBAC::getUserPermissions($rolesId);

        return ApiResponse::createOk($userData);
    }

    //编辑资料-Patch
    public function Patch()
    {
        //传入必要参数
        $lDef_ParamData = [
            'id' => request()->uid,
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
        //传入必要参数
        $lDef_ParamData = [
            'id' => request()->uid,
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
        $lDef_ParamData = [
            'id' => request()->uid,
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

}
