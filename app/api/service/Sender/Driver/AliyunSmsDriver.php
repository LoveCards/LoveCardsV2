<?php

namespace app\api\service\Sender\Driver;

use app\api\service\Sender\Contract\AbstractDriver;
use app\api\service\Sender\Contract\Message;
use app\api\service\Sender\Contract\SendResult;

class AliyunSmsDriver extends AbstractDriver
{
    public static function supportedTypes(): array
    {
        return ['code'];
    }

    public static function meta(): array
    {
        return [
            'type'        => 'aliyun_sms',
            'channelType' => 'sms',
            'name'        => '阿里云短信',
            'icon'        => 'mdi-cellphone',
            'fields'      => [
                ['key' => 'access_key',    'label' => 'AccessKey',    'type' => 'text'],
                ['key' => 'secret_key',    'label' => 'SecretKey',    'type' => 'password'],
                ['key' => 'sign_name',     'label' => '短信签名',      'type' => 'text'],
                ['key' => 'template_code', 'label' => '模板编码',      'type' => 'text'],
            ],
        ];
    }

    public function send(Message $message): SendResult
    {
        $templateCode = $this->config['template_code'] ?? '';
        $signName = $this->config['sign_name'] ?? '';

        if (empty($templateCode) || empty($signName)) {
            return SendResult::fail('sms', '短信配置不完整：缺少模板编码或签名');
        }

        $params = [
            'code'   => $message->code,
            'expire' => (string) $message->expire,
        ];

        // TODO: 接入阿里云 SMS SDK
        return SendResult::fail('sms', '阿里云短信驱动尚未实现');
    }
}
