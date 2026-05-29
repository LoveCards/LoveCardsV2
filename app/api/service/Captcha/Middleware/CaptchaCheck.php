<?php

namespace app\api\service\Captcha\Middleware;

use think\facade\Request;
use app\api\service\Captcha\Captcha;
use app\api\ApiResponse;

class CaptchaCheck
{
    public function handle($request, \Closure $next, ...$args)
    {
        $type = '';

        if (!empty($args)) {
            $first = $args[0];
            if (is_string($first)) {
                $type = $first;
            } elseif (is_array($first)) {
                $type = $first['type'] ?? '';
            }
        }

        if (empty($type)) {
            return $next($request);
        }

        if (!Captcha::isEnabled($type)) {
            return $next($request);
        }

        try {
            $driver = Captcha::driver($type);
        } catch (\Throwable $e) {
            return $next($request);
        }

        $params = $this->collectParams($type);

        if (!$driver->verify($params)) {
            return ApiResponse::createUnauthorized('验证失败');
        }

        return $next($request);
    }

    private function collectParams(string $type): array
    {
        return match ($type) {
            'captcha' => [
                'lot_number'     => Request::param('lot_number', ''),
                'captcha_output' => Request::param('captcha_output', ''),
                'pass_token'     => Request::param('pass_token', ''),
                'gen_time'       => Request::param('gen_time', ''),
                'image_id'       => Request::param('captcha_id', ''),
                'code'           => Request::param('captcha_code', ''),
            ],
            default   => Request::param(),
        };
    }
}
