<?php

namespace app\api\service\Captcha\Driver;

use app\api\service\Captcha\Contract\AbstractDriver;
use app\common\service\Config as ConfigService;

class GeetestDriver extends AbstractDriver
{
    public static function type(): string
    {
        return 'captcha';
    }

    public static function meta(): array
    {
        return [
            'slug'   => 'geetest_v4',
            'type'   => 'captcha',
            'name'   => '极验验证 v4',
            'icon'   => 'mdi-shield-check',
            'fields' => [
                ['key' => 'status', 'label' => '启用状态', 'type' => 'checkbox'],
                ['key' => 'id',     'label' => 'Captcha ID', 'type' => 'text'],
                ['key' => 'key',    'label' => 'Captcha Key', 'type' => 'password'],
            ],
        ];
    }

    public function generate(array $params): array
    {
        $captchaId = ConfigService::get('captcha_geetest_v4.id', '');
        $status    = ConfigService::get('captcha_geetest_v4.status', false);

        return [
            'captcha_id' => $captchaId,
            'status'     => $status,
            'driver'     => 'geetest_v4',
        ];
    }

    public function verify(array $params): bool
    {
        $lotNumber     = $params['lot_number'] ?? '';
        $captchaOutput = $params['captcha_output'] ?? '';
        $passToken     = $params['pass_token'] ?? '';
        $genTime       = $params['gen_time'] ?? '';

        if (empty($lotNumber) || empty($captchaOutput)) {
            return false;
        }

        $captchaId  = ConfigService::get('captcha_geetest_v4.id', '');
        $captchaKey = ConfigService::get('captcha_geetest_v4.key', '');

        if (empty($captchaId) || empty($captchaKey)) {
            return false;
        }

        $signToken = hash_hmac('sha256', $lotNumber, $captchaKey);

        $query = http_build_query([
            'lot_number'     => $lotNumber,
            'captcha_output' => $captchaOutput,
            'pass_token'     => $passToken,
            'gen_time'       => $genTime,
            'sign_token'     => $signToken,
        ]);

        $url = sprintf('http://gcaptcha4.geetest.com/validate?captcha_id=%s', $captchaId);

        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $query,
                'timeout' => 5,
            ],
        ];

        $context = stream_context_create($options);
        $result  = @file_get_contents($url, false, $context);

        if ($result === false) {
            return false;
        }

        $obj = json_decode($result, true);
        return isset($obj['result']) && $obj['result'] === 'success';
    }
}
