<?php

namespace app\api\middleware;

use think\Container;

use app\common\ConfigFacade;
use app\common\Export;
use jwt\Jwt;

class JwtAuthCheck
{
    public function handle($tDef_Request, \Closure $tDef_next)
    {
        //头部取authorization需要特殊伪静态
        $token = $tDef_Request->header('authorization');
        //是否有token
        if ($token != null) {
            //处理token
            $token = preg_replace('/^Bearer\s+/', '', $token);
            //验证token
            $data = Jwt::CheckToken($token);
            if ($data['status']) {
                //jwt校验通过并传递参数
                $tDef_Request->JwtData = $data['data'];
            } else {
                //jwt校验不通过
                return Export::Create($data['msg'], 401, '登入失效，请重新登入'); //Token未通过校验
            }
        } else {
            if (!ConfigFacade::mArraySearchConfigKey('VisitorMode')[0]) {
                return Export::Create(null, 401, '请先登入'); //Token不存在
            } else {
                //jwt校验通过并传递参数
                $tDef_Request->JwtData = [
                    'uid' => '0',
                    'token' => null,
                ];
            }
        }

        return $tDef_next($tDef_Request);
    }
}
