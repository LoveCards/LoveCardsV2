<?php

namespace app\api\middleware;

use jwt\Jwt;

use app\api\controller\ApiResponse;

class JwtAuthLogout
{
    public function handle($tDef_Request, \Closure $tDef_next)
    {
        $token = $tDef_Request->header('authorization');
        if ($token != null) {
            $token = preg_replace('/^Bearer\s+/', '', $token);
            //删除缓存Token即不可续订
            Jwt::DeleteToken($token);
        } else {
            return ApiResponse::createUnauthorized('Unauthorized');
        }
        return $tDef_next($tDef_Request);
    }
}
