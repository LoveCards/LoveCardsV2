<?php

namespace app\api\service\Storage\DirectUpload;

interface DirectUploadProvider
{
    public function getType(): string;

    public function isAvailable(): bool;

    public function getUploadCredential(
        string $filename,
        string $mime,
        int $size,
        string $path,
        int $expire = 3600
    ): array;

    public function confirmUpload(string $driverPath): bool;

    public function getUploadUrl(): string;
}