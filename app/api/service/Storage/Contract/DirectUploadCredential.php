<?php

namespace app\api\service\Storage\Contract;

class DirectUploadCredential
{
    public function __construct(
        public readonly string $url,
        public readonly string $method,
        public readonly array $headers,
        public readonly array $formData,
        public readonly int $expire,
    ) {}
}
