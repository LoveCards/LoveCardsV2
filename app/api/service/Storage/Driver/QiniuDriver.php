<?php

namespace app\api\service\Storage\Driver;

use think\file\UploadedFile;
use app\api\service\Storage\Contract\AbstractDriver;
use app\api\service\Storage\Contract\StorageResult;
use app\api\service\Storage\Contract\HasDirectUpload;
use app\api\service\Storage\Contract\DirectUploadCredential;
use app\api\ApiException;

class QiniuDriver extends AbstractDriver implements HasDirectUpload
{
    public function getType(): string
    {
        return 'qiniu';
    }

    public static function meta(): array
    {
        return [
            'type' => 'qiniu',
            'name' => '七牛云',
            'icon' => 'mdi-cloud',
            'fields' => [
                ['key' => 'access_key', 'label' => 'AccessKey', 'type' => 'text'],
                ['key' => 'secret_key', 'label' => 'SecretKey', 'type' => 'password'],
                ['key' => 'bucket', 'label' => 'Bucket', 'type' => 'text'],
                ['key' => 'domain', 'label' => '域名', 'type' => 'text'],
                ['key' => 'allow_mime_types', 'label' => '允许的MIME类型', 'type' => 'text'],
                ['key' => 'max_file_size', 'label' => '最大文件大小(字节)', 'type' => 'number'],
                ['key' => 'path_template', 'label' => '路径模板', 'type' => 'text'],
            ],
        ];
    }

    public function upload(UploadedFile $file, string $path): StorageResult
    {
        $this->validateFile($file);

        $accessKey = $this->config['access_key'] ?? '';
        $secretKey = $this->config['secret_key'] ?? '';
        $bucket = $this->config['bucket'] ?? '';

        $auth = new \Qiniu\Auth($accessKey, $secretKey);
        $token = $auth->uploadToken($bucket);

        $uploadManager = new \Qiniu\Storage\UploadManager();
        list($result, $error) = $uploadManager->put($token, $path, file_get_contents($file->getPathname()), [
            'mimeType' => $this->detectMime($file->getPathname()),
        ]);

        if ($error !== null) {
            throw new ApiException('七牛上传失败: ' . $error->message());
        }

        $mime = $this->detectMime($file->getPathname());

        return new StorageResult([
            'id' => 0,
            'url' => $this->getUrl($path),
            'path' => $path,
            'driver_path' => $path,
            'size' => $file->getSize(),
            'mime_type' => $mime,
            'original_name' => $file->getOriginalName(),
            'channel_slug' => $this->channelSlug,
        ]);
    }

    public function delete(string $driverPath): bool
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

    public function getDirectUploadUrl(): string
    {
        return 'https://up.qiniup.com';
    }

    public function getUploadCredential(
        string $filename,
        string $mime,
        int $size,
        string $path,
        int $expire = 3600
    ): DirectUploadCredential {
        $this->validateDirectUpload($mime, $size);

        $maxSize = (int) ($this->config['max_file_size'] ?? 0);
        $accessKey = $this->config['access_key'];
        $secretKey = $this->config['secret_key'];
        $bucket = $this->config['bucket'];

        $key = $path;
        $deadline = time() + $expire;

        $policy = json_encode([
            'scope' => $bucket . ':' . $key,
            'deadline' => $deadline,
            'mimeLimit' => $mime,
            'fsizeLimit' => $maxSize > 0 ? $maxSize : 52428800,
        ]);

        $policyEncoded = $this->base64UrlEncode($policy);
        $signature = hash_hmac('sha1', $policyEncoded, $secretKey, true);
        $signatureEncoded = $this->base64UrlEncode($signature);

        $token = $accessKey . ':' . $policyEncoded . ':' . $signatureEncoded;

        return new DirectUploadCredential(
            url: $this->getDirectUploadUrl(),
            method: 'POST',
            headers: [],
            formData: [
                'token' => $token,
                'key' => $key,
                'filename' => $filename,
            ],
            expire: $expire,
        );
    }

    private function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
