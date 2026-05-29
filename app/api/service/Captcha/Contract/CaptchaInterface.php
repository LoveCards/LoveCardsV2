<?php

namespace app\api\service\Captcha\Contract;

interface CaptchaInterface
{
    public function generate(array $params): array;

    public function verify(array $params): bool;

    public static function meta(): array;

    public static function type(): string;
}
