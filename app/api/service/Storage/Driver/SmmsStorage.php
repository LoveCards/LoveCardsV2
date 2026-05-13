<?php

namespace app\api\service\Storage\Driver;

use think\file\UploadedFile;
use app\api\service\Storage\Driver\AbstractStorage;

class SmmsStorage extends AbstractStorage
{
    private const API_URL = 'https://sm.ms/api/v2/upload';

    public function getType(): string
    {
        return 'api';
    }

    public function doUpload(UploadedFile $file, string $path): array
    {
        $apiKey = $this->config['api_key'] ?? '';

        $fileContent = file_get_contents($file->getPathname());
        $fileName = $file->getOriginalName();

        $response = $this->request('POST', self::API_URL, [
            'headers' => [
                'Authorization' => $apiKey,
            ],
            'multipart' => [
                [
                    'name' => 'smfile',
                    'contents' => $fileContent,
                    'filename' => $fileName,
                ],
            ],
        ]);

        if ($response['code'] !== 'success') {
            throw new \app\api\ApiException('SMMS上传失败: ' . ($response['msg'] ?? '未知错误'));
        }

        $data = $response['data'];

        return [
            'path' => $path,
            'url' => $data['url'] ?? '',
            'driver_path' => $data['hash'] ?? $data['filename'] ?? '',
        ];
    }

    public function doDelete(string $driverPath): bool
    {
        return false;
    }

    public function getUrl(string $driverPath): string
    {
        return $driverPath;
    }

    private function request(string $method, string $url, array $options = []): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);

            if (isset($options['multipart'])) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $options['multipart']);
            }

            if (isset($options['headers'])) {
                $headers = [];
                foreach ($options['headers'] as $key => $value) {
                    $headers[] = "$key: $value";
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }
        }

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \app\api\ApiException('HTTP请求失败: ' . $error);
        }

        return json_decode($response, true) ?? [];
    }
}