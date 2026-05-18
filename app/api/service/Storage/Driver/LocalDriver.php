<?php

namespace app\api\service\Storage\Driver;

use think\facade\Filesystem;
use think\file\UploadedFile;
use app\api\service\Storage\Contract\AbstractDriver;
use app\api\service\Storage\Contract\StorageResult;

class LocalDriver extends AbstractDriver
{
    public function getType(): string
    {
        return 'local';
    }

    public static function meta(): array
    {
        return [
            'type' => 'local',
            'name' => '本地存储',
            'icon' => 'mdi-harddisk',
            'fields' => [
                ['key' => 'root', 'label' => '存储根目录', 'type' => 'text'],
                ['key' => 'url_prefix', 'label' => 'URL前缀', 'type' => 'text'],
                ['key' => 'allow_mime_types', 'label' => '允许的MIME类型', 'type' => 'text'],
                ['key' => 'max_file_size', 'label' => '最大文件大小(字节)', 'type' => 'number'],
                ['key' => 'path_template', 'label' => '路径模板', 'type' => 'text'],
            ],
        ];
    }

    public function upload(UploadedFile $file, string $path): StorageResult
    {
        $this->validateFile($file);

        $savePath = str_replace('\\', '/', $path);
        Filesystem::disk('public')->putFileAs(dirname($savePath), $file, basename($savePath));

        $url = $this->getUrl($savePath);
        $mime = $this->detectMime($file->getPathname());

        return new StorageResult([
            'id' => 0,
            'url' => $url,
            'path' => $savePath,
            'driver_path' => $savePath,
            'size' => $file->getSize(),
            'mime_type' => $mime,
            'original_name' => $file->getOriginalName(),
            'channel_slug' => $this->channelSlug,
        ]);
    }

    public function delete(string $driverPath): bool
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
