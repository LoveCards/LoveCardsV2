<?php

namespace app\api\service\Sender\Contract;

class Message
{
    public string $channelType;
    public string|array $to;
    public string $type;
    public string $code;
    public int $expire;
    public ?string $template;
    public array $vars;
    public string $body;

    public function __construct()
    {
        $this->channelType = '';
        $this->to = '';
        $this->type = 'code';
        $this->code = '';
        $this->expire = 5;
        $this->template = null;
        $this->vars = [];
        $this->body = '';
    }

    public static function code(string $channelType, string $to, string $code, int $expire = 5): self
    {
        $msg = new self();
        $msg->channelType = $channelType;
        $msg->to = $to;
        $msg->type = 'code';
        $msg->code = $code;
        $msg->expire = $expire;
        return $msg;
    }

    public static function notify(string $channelType, string $to, string $template, array $vars = []): self
    {
        $msg = new self();
        $msg->channelType = $channelType;
        $msg->to = $to;
        $msg->type = 'notify';
        $msg->template = $template;
        $msg->vars = $vars;
        return $msg;
    }
}
