<?php

namespace app\api\middleware;

use app\api\service\Config as ConfigService;
use app\api\service\Users as UsersService;
use app\api\ApiResponse;
use app\common\extend\jwt\Jwt;

class JwtAuthCheck
{
    public function handle($request, \Closure $next)
    {
        $token = $request->header('authorization');

        if ($token != null) {
            $token = preg_replace('/^Bearer\s+/', '', $token);
            try {
                $data = Jwt::checkToken($token);
                $uid = $data['data']['uid'];
                $user = UsersService::Get($uid);

                if (!$user || !$user->id) {
                    throw \app\api\ApiException::unauthorized('用户不存在', \app\api\ApiException::CODE_USER_NOT_FOUND);
                }

                $request->uid = (int) $uid;
                $request->user = $user;
                $request->rolesId = json_decode($user->roles_id, true) ?: [];
                $request->JwtData = $data['data'];

                if (isset($data['data']['token']) && $data['data']['token'] !== null) {
                    $request->newToken = $data['data']['token'];
                }
            } catch (\app\api\ApiException $e) {
                if (!ConfigService::get('core.visitor_mode')) {
                    return $e->exceptionHandle();
                }
                $this->setVisitor($request);
            }
        } else {
            if (!ConfigService::get('core.visitor_mode')) {
                return ApiResponse::createUnauthorized('请先登入');
            }
            $this->setVisitor($request);
        }

        $response = $next($request);

        if (isset($request->newToken)) {
            $response->header('X-New-Token', $request->newToken);
        }

        return $response;
    }

    private function setVisitor($request): void
    {
        $request->uid = 0;
        $request->user = null;
        $request->rolesId = [4];
        $request->JwtData = [
            'uid' => '0',
            'token' => null,
        ];
    }
}
