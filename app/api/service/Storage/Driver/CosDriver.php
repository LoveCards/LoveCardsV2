<?php

namespace app\api\service\Storage\Driver;

use think\file\UploadedFile;
use app\api\service\Storage\Contract\AbstractDriver;
use app\api\service\Storage\Contract\StorageResult;
use app\api\service\Storage\Contract\HasDirectUpload;
use app\api\service\Storage\Contract\DirectUploadCredential;
use app\api\ApiException;
use Qcloud\Cos\Signature;
use GuzzleHttp\Psr7\Request;

class CosDriver extends AbstractDriver implements HasDirectUpload
{
    private $signature;

    public function getType(): string
    {
        return 'cos';
    }

    public static function meta(): array
    {
        return [
            'type' => 'cos',
            'name' => '腾讯云 COS',
            'icon' => 'mdi-cloud',
            'fields' => [
                ['key' => 'secret_id', 'label' => 'SecretId', 'type' => 'text'],
                ['key' => 'secret_key', 'label' => 'SecretKey', 'type' => 'password'],
                ['key' => 'bucket', 'label' => 'Bucket', 'type' => 'text'],
                ['key' => 'region', 'label' => 'Region', 'type' => 'text'],
                ['key' => 'cdn_url', 'label' => 'CDN域名', 'type' => 'text'],
                ['key' => 'allow_mime_types', 'label' => '允许的MIME类型', 'type' => 'text'],
                ['key' => 'max_file_size', 'label' => '最大文件大小(字节)', 'type' => 'number'],
                ['key' => 'path_template', 'label' => '路径模板', 'type' => 'text'],
            ],
        ];
    }

    public function upload(UploadedFile $file, string $path): StorageResult
    {
        $this->validateFile($file);

        $bucket = $this->config['bucket'] ?? '';
        $region = $this->config['region'] ?? '';

        $mime = $this->detectMime($file->getPathname());
        $fileSize = $file->getSize();

        $host = "{$bucket}.cos.{$region}.myqcloud.com";
        $url = "https://{$host}/{$path}";

        $fp = fopen($file->getPathname(), 'r');

        $auth = $this->getSignature()->createAuthorization(
            new Request('PUT', $url, ['Content-Type' => $mime], ''),
            '+30 minutes'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_UPLOAD, true);
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, $fileSize);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $auth,
            'Content-Type: ' . $mime,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        fclose($fp);
        curl_close($ch);

        if ($response === false || $error) {
            throw new ApiException('COS连接失败: ' . $error);
        }

        if ($httpCode !== 200 && $httpCode !== 204) {
            throw new ApiException('COS上传失败: HTTP ' . $httpCode);
        }

        return new StorageResult([
            'id' => 0,
            'url' => $this->getUrl($path),
            'path' => $path,
            'driver_path' => $path,
            'size' => $fileSize,
            'mime_type' => $mime,
            'original_name' => $file->getOriginalName(),
            'channel_slug' => $this->channelSlug,
        ]);
    }

    public function delete(string $driverPath): bool
    {
        $bucket = $this->config['bucket'] ?? '';
        $region = $this->config['region'] ?? '';

        $host = "{$bucket}.cos.{$region}.myqcloud.com";
        $url = "https://{$host}/{$driverPath}";

        $auth = $this->getSignature()->createAuthorization(
            new Request('DELETE', $url, [], ''),
            '+30 minutes'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $auth,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new ApiException('COS删除失败: ' . $error);
        }

        return ($httpCode === 200 || $httpCode === 204);
    }

    public function getUrl(string $driverPath): string
    {
        return rtrim($this->config['cdn_url'] ?? '', '/') . '/' . ltrim($driverPath, '/');
    }

    public function getDirectUploadUrl(): string
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
    ): DirectUploadCredential {
        $this->validateDirectUpload($mime, $size);

        $maxSize = (int) ($this->config['max_file_size'] ?? 0);
        $secretId = $this->config['secret_id'];
        $secretKey = $this->config['secret_key'];

        $key = $path;
        $expireTime = time() + $expire;

        $policy = base64_encode(json_encode([
            'expiration' => ['expired' => $expireTime],
            'conditions' => [
                ['content-length-range', 0, $maxSize > 0 ? $maxSize : 52428800],
                ['eq', '$Content-Type', $mime],
                ['eq', '$key', $key],
            ],
        ]));

        $signature = base64_encode(hash_hmac('sha1', $policy, $secretKey, true));

        return new DirectUploadCredential(
            url: $this->getDirectUploadUrl(),
            method: 'POST',
            headers: [],
            formData: [
                'key' => $key,
                'policy' => $policy,
                'signature' => $signature,
                'q-sign-algorithm' => 'sha1',
                'q-ak' => $secretId,
                'x-cos-security-token' => $this->config['session_token'] ?? '',
            ],
            expire: $expire,
        );
    }

    private function getSignature(): Signature
    {
        if ($this->signature === null) {
            $this->signature = new Signature(
                $this->config['secret_id'] ?? '',
                $this->config['secret_key'] ?? '',
                [
                    'signHost' => true,
                    'timezone' => date_default_timezone_get() ?: 'Asia/Shanghai',
                ]
            );
        }
        return $this->signature;
    }
}
