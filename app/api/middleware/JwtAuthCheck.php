<?php

namespace app\api\middleware;

use think\facade\Config;
use app\api\ApiResponse;
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
                if (!Config::get('master.System.VisitorMode')) {
                    //jwt校验不通过
                    return ApiResponse::createUnauthorized('登入失效，请重新登入', $data['msg']); //Token未通过校验
                } else {
                    //jwt校验通过并传递参数
                    $tDef_Request->JwtData = [
                        'uid' => '0',
                        'token' => null,
                    ];
                }
            }
        } else {
            if (!Config::get('master.System.VisitorMode')) {
                return ApiResponse::createUnauthorized('请先登入'); //Token不存在
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
