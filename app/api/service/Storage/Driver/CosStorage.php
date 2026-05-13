<?php

namespace app\api\service\Storage\Driver;

use think\file\UploadedFile;
use app\api\service\Storage\Driver\AbstractStorage;
use Qcloud\Cos\Signature;
use GuzzleHttp\Psr7\Request;

class CosStorage extends AbstractStorage
{
    private $signature;

    public function getType(): string
    {
        return 'cos';
    }

    public function doUpload(UploadedFile $file, string $path): array
    {
        $bucket = $this->config['bucket'] ?? '';
        $region = $this->config['region'] ?? '';
        $cdnUrl = $this->config['cdn_url'] ?? '';

        $content = file_get_contents($file->getPathname());
        $mime = $this->detectMimeType($file->getPathname());

        $host = "{$bucket}.cos.{$region}.myqcloud.com";
        $url = "https://{$host}/{$path}";

        $auth = $this->getSignature()->createAuthorization(
            new Request('PUT', $url, ['Content-Type' => $mime], $content),
            '+30 minutes'
        );

        $ch = curl_init();

        $tmpFile = fopen('php://temp', 'w+');
        fwrite($tmpFile, $content);
        rewind($tmpFile);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_UPLOAD, true);
        curl_setopt($ch, CURLOPT_INFILE, $tmpFile);
        curl_setopt($ch, CURLOPT_INFILESIZE, strlen($content));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: ' . $auth,
            'Content-Type: ' . $mime,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        fclose($tmpFile);
        curl_close($ch);

        if ($response === false || $error) {
            throw new \app\api\ApiException('COS连接失败: ' . $error . ' (URL: ' . $url . ')');
        }

        if ($httpCode !== 200 && $httpCode !== 204) {
            throw new \app\api\ApiException('COS上传失败: HTTP ' . $httpCode . ' - ' . $response);
        }

        return [
            'path' => $path,
            'url' => rtrim($cdnUrl, '/') . '/' . ltrim($path, '/'),
            'driver_path' => $path,
        ];
    }

    public function doDelete(string $driverPath): bool
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
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
            throw new \app\api\ApiException('COS删除失败: ' . $error);
        }

        return ($httpCode === 200 || $httpCode === 204);
    }

    public function getUrl(string $driverPath): string
    {
        return rtrim($this->config['cdn_url'] ?? '', '/') . '/' . ltrim($driverPath, '/');
    }

    private function getSignature(): Signature
    {
        if ($this->signature === null) {
            $secretId = $this->config['secret_id'] ?? '';
            $secretKey = $this->config['secret_key'] ?? '';

            $this->signature = new Signature($secretId, $secretKey, [
                'signHost' => true,
                'timezone' => date_default_timezone_get() ?: 'Asia/Shanghai',
            ]);
        }

        return $this->signature;
    }
}