<?php

namespace app\api\service\Storage;

use think\file\UploadedFile;
use app\api\service\Storage\Contract\StorageResult;

class ChannelUploader
{
    private string $slug;

    public function __construct(string $slug)
    {
        $this->slug = $slug;
    }

    public function upload(UploadedFile $file, string $path): StorageResult
    {
        $driver = StorageFactory::make($this->slug);
        return $driver->upload($file, $path);
    }
}
