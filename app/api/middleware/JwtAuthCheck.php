<?php

namespace app\api\middleware;

use think\facade\Config;
use app\api\ApiResponse;
use app\common\extend\jwt\Jwt;

class JwtAuthCheck
{
    public function handle($tDef_Request, \Closure $tDef_next)
    {
        $token = $tDef_Request->header('authorization');
        if ($token != null) {
            $token = preg_replace('/^Bearer\s+/', '', $token);
            try {
                $data = Jwt::checkToken($token);
                $tDef_Request->JwtData = $data['data'];
            } catch (\app\api\ApiException $e) {
                if (!Config::get('master.System.VisitorMode')) {
                    return $e->exceptionHandle();
                }
                $tDef_Request->JwtData = [
                    'uid' => '0',
                    'token' => null,
                ];
            }
        } else {
            if (!Config::get('master.System.VisitorMode')) {
                return ApiResponse::createUnauthorized('请先登入');
            } else {
                $tDef_Request->JwtData = [
                    'uid' => '0',
                    'token' => null,
                ];
            }
        }

        return $tDef_next($tDef_Request);
    }
}
