<?php

namespace app\api\service\Storage\DirectUpload;

use app\api\ApiException;

class CosDirectUpload extends AbstractDirectUpload
{
    public function isAvailable(): bool
    {
        if (!parent::isAvailable()) {
            return false;
        }
        return !empty($this->config['secret_id'])
            && !empty($this->config['secret_key'])
            && !empty($this->config['bucket'])
            && !empty($this->config['region']);
    }

    public function getUploadUrl(): string
    {
        $bucket = $this->config['bucket'];
        $region = $this->config['region'];
        return "https://{$bucket}.cos.{$region}.myqcloud.com";
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
        $secretId = $this->config['secret_id'];
        $secretKey = $this->config['secret_key'];
        $region = $this->config['region'];

        $key = $path;

        $now = time();
        $expireTime = $now + $expire;

        $policy = base64_encode(json_encode([
            'expiration' => [
                'expired' => $expireTime,
            ],
            'conditions' => [
                ['content-length-range', 0, $maxSize > 0 ? $maxSize : 52428800],
                ['eq', '$Content-Type', $mime],
                ['eq', '$key', $key],
            ],
        ]));

        $signature = base64_encode(hash_hmac('sha1', $policy, $secretKey, true));

        return [
            'upload_url' => $this->getUploadUrl(),
            'form_data' => [
                'key' => $key,
                'policy' => $policy,
                'signature' => $signature,
                'q-sign-algorithm' => 'sha1',
                'q-ak' => $secretId,
                'x-cos-security-token' => $this->config['session_token'] ?? '',
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
        return rtrim($this->config['cdn_url'] ?? '', '/') . '/' . ltrim($driverPath, '/');
    }
}