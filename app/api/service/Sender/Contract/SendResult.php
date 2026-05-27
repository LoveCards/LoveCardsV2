<?php

namespace app\api\service\Sender\Contract;

class SendResult
{
    public bool $success;
    public string $channelType;
    public ?string $messageId;
    public ?string $error;

    public function __construct(
        bool $success,
        string $channelType,
        ?string $messageId = null,
        ?string $error = null
    ) {
        $this->success = $success;
        $this->channelType = $channelType;
        $this->messageId = $messageId;
        $this->error = $error;
    }

    public static function ok(string $channelType, ?string $messageId = null): self
    {
        return new self(true, $channelType, $messageId);
    }

    public static function fail(string $channelType, string $error): self
    {
        return new self(false, $channelType, null, $error);
    }
}
