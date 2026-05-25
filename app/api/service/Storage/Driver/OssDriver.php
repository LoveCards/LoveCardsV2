<?php

namespace app\api\service\Storage\Driver;

use think\file\UploadedFile;
use app\api\service\Storage\Contract\AbstractDriver;
use app\api\service\Storage\Contract\StorageResult;
use app\api\service\Storage\Contract\HasDirectUpload;
use app\api\service\Storage\Contract\DirectUploadCredential;
use app\api\ApiException;

class OssDriver extends AbstractDriver implements HasDirectUpload
{
    private $ossClient = null;

    public function getType(): string
    {
        return 'oss';
    }

    public static function meta(): array
    {
        return [
            'type' => 'oss',
            'name' => '阿里云 OSS',
            'icon' => 'mdi-cloud',
            'fields' => [
                ['key' => 'access_key', 'label' => 'AccessKey', 'type' => 'text'],
                ['key' => 'secret_key', 'label' => 'SecretKey', 'type' => 'password'],
                ['key' => 'bucket', 'label' => 'Bucket', 'type' => 'text'],
                ['key' => 'endpoint', 'label' => 'Endpoint', 'type' => 'text'],
                ['key' => 'url_prefix', 'label' => 'URL前缀', 'type' => 'text'],
                ['key' => 'allow_mime_types', 'label' => '允许的MIME类型', 'type' => 'text'],
                ['key' => 'max_file_size', 'label' => '最大文件大小(字节)', 'type' => 'number'],
                ['key' => 'path_template', 'label' => '路径模板', 'type' => 'text'],
            ],
        ];
    }

    protected function getOssClient()
    {
        if ($this->ossClient === null) {
            $this->ossClient = new \OSS\OssClient(
                $this->config['access_key'] ?? '',
                $this->config['secret_key'] ?? '',
                $this->config['endpoint'] ?? ''
            );
        }
        return $this->ossClient;
    }

    public function upload(UploadedFile $file, string $path): StorageResult
    {
        $this->validateFile($file);

        $bucket = $this->config['bucket'] ?? '';
        $this->getOssClient()->uploadFile($bucket, $path, $file->getPathname());

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

    public function getDirectUploadUrl(): string
    {
        return 'https://' . $this->config['bucket'] . '.' . $this->config['endpoint'];
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
        $accessKeyId = $this->config['access_key'];
        $accessKeySecret = $this->config['secret_key'];

        $key = $path;

        $policy = base64_encode(json_encode([
            'expiration' => date('Y-m-d\TH:i:s\Z', time() + $expire),
            'conditions' => [
                ['content-length-range', 0, $maxSize > 0 ? $maxSize : 52428800],
                ['eq', '$Content-Type', $mime],
                ['eq', '$key', $key],
            ],
        ]));

        $signature = base64_encode(hash_hmac('sha1', $policy, $accessKeySecret, true));

        return new DirectUploadCredential(
            url: $this->getDirectUploadUrl(),
            method: 'POST',
            headers: [],
            formData: [
                'key' => $key,
                'policy' => $policy,
                'signature' => $signature,
                'OSSAccessKeyId' => $accessKeyId,
                'Content-Disposition' => 'attachment; filename="' . rawurlencode($filename) . '"',
            ],
            expire: $expire,
        );
    }
}
