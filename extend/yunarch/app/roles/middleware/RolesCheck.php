<?php

namespace yunarch\app\roles\middleware;

use think\facade\Request;

use yunarch\app\roles\facade\Roles;

//定制部分
use app\api\service\Users as UsersService;
use app\common\Export;

class RolesCheck
{
    public function handle($request, \Closure $next)
    {

        $jwtData = request()->JwtData;

        //查询用户数据
        $userData = UsersService::Get($jwtData['uid']);
        //获取用户分组
        $user_roles_id = json_decode($userData['data']->roles_id);
        //验证权限
        if (!Roles::checkIdBaseUrlPass($user_roles_id)) {
            return Export::Create(null, 401, '权限不足');
        }

        // 传递当前管理员全部数据
        // $tDef_Request->NowAdminData = $this->attrLDefAdminAllData;

        return $next($request);
    }
}
