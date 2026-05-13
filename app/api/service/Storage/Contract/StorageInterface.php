<?php

namespace app\api\service\Storage\Contract;

use think\file\UploadedFile;

interface StorageInterface
{
    public function upload(UploadedFile $file, string $path): StorageResult;

    public function delete(string $driverPath): bool;

    public function exists(string $driverPath): bool;

    public function getUrl(string $driverPath): string;

    public function supportedMimeTypes(): array;

    public function maxFileSize(): int;

    public function getConfig(): array;

    public function getType(): string;
}