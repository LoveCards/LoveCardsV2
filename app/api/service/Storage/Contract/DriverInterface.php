<?php

namespace app\api\service\Storage\Contract;

use think\file\UploadedFile;

interface DriverInterface
{
    public function getType(): string;

    public function upload(UploadedFile $file, string $path): StorageResult;

    public function delete(string $driverPath): bool;

    public function getUrl(string $driverPath): string;

    public static function meta(): array;
}
