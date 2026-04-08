<?php

namespace app\api\middleware;

use app\common\extend\jwt\Jwt;
use app\api\ApiResponse;

class JwtAuthLogout
{
    public function handle($tDef_Request, \Closure $tDef_next)
    {
        $token = $tDef_Request->header('authorization');
        if ($token != null) {
            $token = preg_replace('/^Bearer\s+/', '', $token);
            Jwt::deleteToken($token);
        } else {
            return ApiResponse::createUnauthorized('Unauthorized');
        }
        return $tDef_next($tDef_Request);
    }
}
