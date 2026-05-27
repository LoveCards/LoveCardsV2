<?php

namespace app\api\service\Sender\Driver;

use mailer\tp6\Mailer;
use app\api\service\Sender\Contract\AbstractDriver;
use app\api\service\Sender\Contract\Message;
use app\api\service\Sender\Contract\SendResult;

class SmtpDriver extends AbstractDriver
{
    public static function meta(): array
    {
        return [
            'type'        => 'smtp',
            'channelType' => 'smtp',
            'name'        => 'SMTP 邮件',
            'icon'        => 'mdi-email',
            'fields'      => [
                ['key' => 'host',     'label' => 'SMTP 服务器',  'type' => 'text'],
                ['key' => 'port',     'label' => '端口',         'type' => 'number'],
                ['key' => 'addr',     'label' => '发件邮箱',     'type' => 'text'],
                ['key' => 'pass',     'label' => '邮箱密码',     'type' => 'password'],
                ['key' => 'name',     'label' => '发件人名称',   'type' => 'text'],
                ['key' => 'security', 'label' => '加密方式',     'type' => 'select', 'options' => [
                    ['value' => 'ssl', 'label' => 'SSL'],
                    ['value' => 'tls', 'label' => 'TLS'],
                    ['value' => '',    'label' => '无加密'],
                ]],
            ],
        ];
    }

    public function send(Message $message): SendResult
    {
        return $message->type === 'code'
            ? $this->sendCode($message)
            : $this->sendNotify($message);
    }

    private function sendCode(Message $message): SendResult
    {
        $subject = '验证码';
        $body = $this->renderTemplate('code', [
            'code'   => $message->code,
            'expire' => $message->expire,
        ]);
        return $this->doSend($message->to, $subject, $body);
    }

    private function sendNotify(Message $message): SendResult
    {
        $subject = $message->vars['subject'] ?? 'LoveCards 通知';
        if ($message->template) {
            $body = $this->renderTemplate($message->template, $message->vars);
        } else {
            $body = $message->body;
        }
        return $this->doSend($message->to, $subject, $body);
    }

    private function doSend(string|array $to, string $subject, string $body): SendResult
    {
        try {
            $this->resetMailerConfig();

            $mailer = new Mailer();
            $result = $mailer->to($to)
                ->subject($subject)
                ->html($body)
                ->send();

            if ($result === false) {
                return SendResult::fail('smtp', 'SMTP 发送失败');
            }

            return SendResult::ok('smtp');
        } catch (\Throwable $e) {
            return SendResult::fail('smtp', $e->getMessage());
        }
    }

    private function resetMailerConfig(): void
    {
        if (!class_exists('\mailer\lib\Config')) {
            return;
        }

        $reflClass = new \ReflectionClass('\mailer\lib\Config');
        $configProp = $reflClass->getProperty('config');
        $configProp->setAccessible(true);
        $isInitProp = $reflClass->getProperty('isInit');
        $isInitProp->setAccessible(true);

        $security = $this->config['security'] ?? '';
        if ($security === '') {
            $security = 'ssl';
        }

        $configProp->setValue(null, [
            'driver'       => 'smtp',
            'host'         => $this->config['host'] ?? '',
            'port'         => $this->config['port'] ?? 465,
            'addr'         => $this->config['addr'] ?? '',
            'pass'         => $this->config['pass'] ?? '',
            'name'         => $this->config['name'] ?? '',
            'content_type' => 'text/html',
            'charset'      => 'utf-8',
            'security'     => $security,
            'debug'        => true,
        ]);
        $isInitProp->setValue(null, true);
    }
}
