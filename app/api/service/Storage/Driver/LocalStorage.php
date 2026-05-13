<?php

namespace app\api\service\Storage\Driver;

use think\facade\Filesystem;
use think\file\UploadedFile;

class LocalStorage extends AbstractStorage
{
    public function getType(): string
    {
        return 'local';
    }

    public function doUpload(UploadedFile $file, string $path): array
    {
        $savePath = Filesystem::disk('public')->putFile('image', $file, function () use ($path) {
            return rtrim(str_replace('\\', '/', $path), '/');
        });

        $urlPrefix = $this->config['url_prefix'] ?? '/storage';
        $fullUrl = request()->scheme() . '://' . request()->host() . $urlPrefix . '/' . $savePath;

        return [
            'path' => $savePath,
            'url' => $fullUrl,
            'driver_path' => $savePath,
        ];
    }

    public function doDelete(string $driverPath): bool
    {
        $basePath = app()->getRootPath() . 'public/storage/';
        $fullPath = realpath($basePath . $driverPath);

        if ($fullPath === false || strpos($fullPath, realpath($basePath)) !== 0) {
            return false;
        }

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return true;
    }

    public function getUrl(string $driverPath): string
    {
        $urlPrefix = $this->config['url_prefix'] ?? '/storage';
        return request()->scheme() . '://' . request()->host() . $urlPrefix . '/' . $driverPath;
    }
}