<?php

namespace app\api\service\Storage\Contract;

interface HasDirectUpload
{
    public function getDirectUploadUrl(): string;

    public function getUploadCredential(
        string $filename,
        string $mime,
        int $size,
        string $path,
        int $expire = 3600
    ): DirectUploadCredential;
}
