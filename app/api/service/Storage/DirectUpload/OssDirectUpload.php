<?php

namespace app\api\service\Storage\DirectUpload;

use app\api\ApiException;

class OssDirectUpload extends AbstractDirectUpload
{
    private ?\OSS\OssClient $ossClient = null;

    public function isAvailable(): bool
    {
        if (!parent::isAvailable()) {
            return false;
        }
        return !empty($this->config['access_key'])
            && !empty($this->config['secret_key'])
            && !empty($this->config['bucket'])
            && !empty($this->config['endpoint']);
    }

    public function getUploadUrl(): string
    {
        return 'https://' . $this->config['bucket'] . '.' . $this->config['endpoint'];
    }

    public function getUploadCredential(
        string $filename,
        string $mime,
        int $size,
        string $path,
        int $expire = 3600
    ): array {
        if (!$this->isMimeAllowed($mime)) {
            throw new ApiException('不支持的文件类型: ' . $mime);
        }

        $maxSize = (int) ($this->config['max_file_size'] ?? 0);
        if ($maxSize > 0 && $size > $maxSize) {
            throw new ApiException('文件大小超出限制');
        }

        $bucket = $this->config['bucket'];
        $accessKeyId = $this->config['access_key'];
        $accessKeySecret = $this->config['secret_key'];
        $endpoint = $this->config['endpoint'];

        $dir = dirname($path) . '/';
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $key = $dir . basename($path) . '.' . $extension;

        $policy = base64_encode(json_encode([
            'expiration' => $this->getExpireTime($expire),
            'conditions' => [
                ['content-length-range', 0, $maxSize > 0 ? $maxSize : 52428800],
                ['eq', '$Content-Type', $mime],
                ['eq', '$key', $key],
            ],
        ]));

        $signature = base64_encode(hash_hmac('sha1', $policy, $accessKeySecret, true));

        return [
            'upload_url' => $this->getUploadUrl(),
            'form_data' => [
                'key' => $key,
                'policy' => $policy,
                'signature' => $signature,
                'OSSAccessKeyId' => $accessKeyId,
                'Content-Disposition' => 'attachment; filename="' . rawurlencode($filename) . '"',
            ],
            'expire' => $expire,
        ];
    }

    public function getDriverPath(string $key): string
    {
        return $key;
    }

    public function getFileUrl(string $driverPath): string
    {
        return $this->config['url_prefix'] . '/' . ltrim($driverPath, '/');
    }
}