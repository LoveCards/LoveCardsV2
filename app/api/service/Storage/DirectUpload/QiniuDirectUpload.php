<?php

namespace app\api\service\Storage\DirectUpload;

use app\api\ApiException;

class QiniuDirectUpload extends AbstractDirectUpload
{
    public function isAvailable(): bool
    {
        if (!parent::isAvailable()) {
            return false;
        }
        return !empty($this->config['access_key'])
            && !empty($this->config['secret_key'])
            && !empty($this->config['bucket']);
    }

    public function getUploadUrl(): string
    {
        return 'https://up.qiniup.com';
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

        $accessKey = $this->config['access_key'];
        $secretKey = $this->config['secret_key'];
        $bucket = $this->config['bucket'];

        $dir = dirname($path) . '/';
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $key = $dir . basename($path) . '.' . $extension;

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

        return [
            'upload_url' => $this->getUploadUrl(),
            'form_data' => [
                'token' => $token,
                'key' => $key,
                'filename' => $filename,
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
        $domain = $this->config['domain'] ?? '';
        if (empty($domain)) {
            return '';
        }
        return rtrim($domain, '/') . '/' . ltrim($driverPath, '/');
    }

    private function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}