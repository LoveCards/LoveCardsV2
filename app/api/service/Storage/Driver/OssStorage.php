<?php

namespace app\api\service\Storage\Driver;

use think\file\UploadedFile;
use app\api\service\Storage\Driver\AbstractStorage;

class OssStorage extends AbstractStorage
{
    private $ossClient = null;

    public function getType(): string
    {
        return 'cloud';
    }

    protected function getOssClient()
    {
        if ($this->ossClient === null) {
            $accessKeyId = $this->config['access_key'] ?? '';
            $accessKeySecret = $this->config['secret_key'] ?? '';

            $this->ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $this->config['endpoint'] ?? '');
        }

        return $this->ossClient;
    }

    public function doUpload(UploadedFile $file, string $path): array
    {
        $bucket = $this->config['bucket'] ?? '';

        $content = file_get_contents($file->getPathname());

        $this->getOssClient()->putObject($bucket, $path, $content);

        return [
            'path' => $path,
            'url' => $this->getUrl($path),
            'driver_path' => $path,
        ];
    }

    public function doDelete(string $driverPath): bool
    {
        $bucket = $this->config['bucket'] ?? '';

        try {
            $this->getOssClient()->deleteObject($bucket, $driverPath);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getUrl(string $driverPath): string
    {
        return $this->config['url_prefix'] . '/' . ltrim($driverPath, '/');
    }
}