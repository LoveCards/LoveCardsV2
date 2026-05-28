<?php

namespace app\api\middleware;

use think\facade\Request;
use think\facade\Config;

use app\api\ApiResponse;

class GeetestCheck
{
    public function handle($request, \Closure $next)
    {
        if (Config::get('master.Geetest.Status') == 0) {
            return $next($request);
        }

        $result = $this->validate(
            Request::param('lot_number'),
            Request::param('captcha_output'),
            Request::param('pass_token'),
            Request::param('gen_time')
        );

        if (!$result) {
            return ApiResponse::createUnauthorized('人机验证失败');
        }

        return $next($request);
    }

    private function validate($lotNumber, $captchaOutput, $passToken, $genTime): bool
    {
        $captcha_id = Config::get('master.Geetest.Id');
        $captcha_key = Config::get('master.Geetest.Key');
        $api_server = 'http://gcaptcha4.geetest.com';

        $sign_token = hash_hmac('sha256', $lotNumber, $captcha_key);

        $query = http_build_query([
            'lot_number'     => $lotNumber,
            'captcha_output' => $captchaOutput,
            'pass_token'     => $passToken,
            'gen_time'       => $genTime,
            'sign_token'     => $sign_token,
        ]);

        $url = sprintf($api_server . '/validate?captcha_id=%s', $captcha_id);

        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $query,
                'timeout' => 5,
            ],
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            return false;
        }

        $obj = json_decode($result, true);
        return isset($obj['result']) && $obj['result'] === 'success';
    }
}
