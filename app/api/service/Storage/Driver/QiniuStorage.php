<?php

namespace app\api\service\Storage\Driver;

use think\file\UploadedFile;
use app\api\service\Storage\Driver\AbstractStorage;

class QiniuStorage extends AbstractStorage
{
    public function getType(): string
    {
        return 'qiniu';
    }

    public function doUpload(UploadedFile $file, string $path): array
    {
        $accessKey = $this->config['access_key'] ?? '';
        $secretKey = $this->config['secret_key'] ?? '';
        $bucket = $this->config['bucket'] ?? '';
        $domain = $this->config['domain'] ?? '';

        $content = file_get_contents($file->getPathname());

        $auth = new \Qiniu\Auth($accessKey, $secretKey);
        $token = $auth->uploadToken($bucket);

        $uploadManager = new \Qiniu\Storage\UploadManager();
        list($result, $error) = $uploadManager->put($token, $path, $content);

        if ($error !== null) {
            throw new \app\api\ApiException('七牛上传失败: ' . $error->message());
        }

        $url = rtrim($domain, '/') . '/' . ltrim($path, '/');

        return [
            'path' => $path,
            'url' => $url,
            'driver_path' => $path,
        ];
    }

    public function doDelete(string $driverPath): bool
    {
        $accessKey = $this->config['access_key'] ?? '';
        $secretKey = $this->config['secret_key'] ?? '';

        try {
            $auth = new \Qiniu\Auth($accessKey, $secretKey);
            $config = new \Qiniu\Config();
            $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);

            $bucket = $this->config['bucket'] ?? '';
            $bucketManager->delete($bucket, $driverPath);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getUrl(string $driverPath): string
    {
        return rtrim($this->config['domain'] ?? '', '/') . '/' . ltrim($driverPath, '/');
    }
}