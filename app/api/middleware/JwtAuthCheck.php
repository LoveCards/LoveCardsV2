<?php

namespace app\api\middleware;

use app\common\service\Config as ConfigService;
use app\api\service\User\Users as UsersService;
use app\api\service\Rbac\RBAC;
use app\api\ApiResponse;
use app\common\infra\Jwt;

class JwtAuthCheck
{
    public function handle($request, \Closure $next)
    {
        $token = $request->header('authorization');

        if ($token != null) {
            $token = preg_replace('/^Bearer\s+/', '', $token);
            try {
                $data = Jwt::verify($token);
                $uid = $data['uid'];
                $user = UsersService::Get($uid);

                if (!$user || !$user->id) {
                    throw \app\api\ApiException::unauthorized('用户不存在', \app\api\ApiException::CODE_USER_NOT_FOUND);
                }

                $request->uid = (int) $uid;
                $request->user = $user;
                $request->rolesId = is_array($user->roles_id) ? $user->roles_id : (json_decode($user->roles_id, true) ?: []);

                // 注入用户能力列表（batch 路由跳过 PermissionCheck 时也能拿到 caps）
                $request->caps = RBAC::getUserCapabilities($request->rolesId);

                if (isset($data['_new_token'])) {
                    $request->newToken = $data['_new_token'];
                }
            } catch (\RuntimeException $e) {
                $apiEx = \app\api\ApiException::unauthorized($e->getMessage());
                if (!ConfigService::get('core.visitor_mode')) {
                    return $apiEx->exceptionHandle();
                }
                $this->setVisitor($request);
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
        $request->rolesId = [config('system.system_roles.guest')];
        // 访客能力
        $request->caps = RBAC::getUserCapabilities($request->rolesId);
    }
}
