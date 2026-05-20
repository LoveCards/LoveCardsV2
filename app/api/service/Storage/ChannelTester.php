<?php

namespace app\api\service\Storage;

use app\api\service\Config as ConfigService;

class ChannelTester
{
    public static function test(string $channel): array
    {
        $config = ConfigService::getGroup('storage_' . $channel);
        if (empty($config)) {
            return ['success' => false, 'message' => '渠道配置不存在'];
        }

        $type = $config['type'] ?? $channel;

        switch ($type) {
            case 'local':
                return self::testLocal($config);
            case 'cos':
                return self::testCos($config);
            case 'oss':
                return self::testOss($config);
            case 'qiniu':
                return self::testQiniu($config);
            default:
                return ['success' => false, 'message' => '不支持的渠道类型'];
        }
    }

    private static function testLocal(array $config): array
    {
        $root = $config['root'] ?? 'public/storage';
        $fullPath = app()->getRootPath() . $root;
        if (!is_dir($fullPath)) {
            @mkdir($fullPath, 0755, true);
        }
        $writable = is_writable($fullPath);
        return [
            'success' => $writable,
            'message' => $writable ? '目录可写' : '目录不可写: ' . $fullPath,
        ];
    }

    private static function testCos(array $config): array
    {
        $secretId = $config['secret_id'] ?? '';
        $secretKey = $config['secret_key'] ?? '';
        $bucket = $config['bucket'] ?? '';
        $region = $config['region'] ?? '';

        if (empty($secretId) || empty($secretKey) || empty($bucket) || empty($region)) {
            return ['success' => false, 'message' => '配置不完整'];
        }

        try {
            $host = "{$bucket}.cos.{$region}.myqcloud.com";
            $url = "https://{$host}/";

            $signTime = (time() - 60) . ';' . (time() + 300);
            $httpString = "get\n/\n\nhost={$host}\n";
            $sha1edHttpString = sha1($httpString);
            $stringToSign = "sha1\n{$signTime}\n{$sha1edHttpString}\n";
            $signKey = hash_hmac('sha1', $signTime, trim($secretKey));
            $signature = hash_hmac('sha1', $stringToSign, $signKey);
            $auth = "q-sign-algorithm=sha1&q-ak=" . trim($secretId) .
                "&q-sign-time={$signTime}&q-key-time={$signTime}" .
                "&q-header-list=host&q-url-param-list=&q-signature={$signature}";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: ' . $auth,
                'Host: ' . $host,
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return ['success' => false, 'message' => '连接失败: ' . $error];
            }

            if ($httpCode === 200 || $httpCode === 204 || $httpCode === 403) {
                return ['success' => true, 'message' => '连接成功 (HTTP ' . $httpCode . ')'];
            }

            return ['success' => false, 'message' => '连接失败 (HTTP ' . $httpCode . ')'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '连接失败: ' . $e->getMessage()];
        }
    }

    private static function testOss(array $config): array
    {
        $accessKey = $config['access_key'] ?? '';
        $secretKey = $config['secret_key'] ?? '';
        $bucket = $config['bucket'] ?? '';
        $endpoint = $config['endpoint'] ?? '';

        if (empty($accessKey) || empty($secretKey) || empty($bucket) || empty($endpoint)) {
            return ['success' => false, 'message' => '配置不完整'];
        }

        try {
            $url = "https://{$bucket}.{$endpoint}/";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return ['success' => false, 'message' => '连接失败: ' . $error];
            }

            if ($httpCode >= 200 && $httpCode < 500) {
                return ['success' => true, 'message' => '连接成功 (HTTP ' . $httpCode . ')'];
            }

            return ['success' => false, 'message' => '连接失败 (HTTP ' . $httpCode . ')'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '连接失败: ' . $e->getMessage()];
        }
    }

    private static function testQiniu(array $config): array
    {
        $accessKey = $config['access_key'] ?? '';
        $secretKey = $config['secret_key'] ?? '';
        $bucket = $config['bucket'] ?? '';

        if (empty($accessKey) || empty($secretKey) || empty($bucket)) {
            return ['success' => false, 'message' => '配置不完整'];
        }

        try {
            $url = "https://rs.qiniu.com/mgr?bucket={$bucket}";

            $token = self::getQiniuToken($accessKey, $secretKey, $bucket, '/mgr');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: QBox ' . $token,
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return ['success' => false, 'message' => '连接失败: ' . $error];
            }

            if ($httpCode === 200 || $httpCode === 401 || $httpCode === 631) {
                return ['success' => true, 'message' => '连接成功 (HTTP ' . $httpCode . ')'];
            }

            return ['success' => false, 'message' => '连接失败 (HTTP ' . $httpCode . ')'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '连接失败: ' . $e->getMessage()];
        }
    }

    private static function getQiniuToken($accessKey, $secretKey, $bucket, $path): string
    {
        $expire = time() + 3600;
        $policy = json_encode(['scope' => $bucket, 'deadline' => $expire]);
        $encodedPolicy = rtrim(strtr(base64_encode($policy), '+/', '-_'), '=');
        $sign = hash_hmac('sha1', $encodedPolicy, $secretKey, true);
        $encodedSign = rtrim(strtr(base64_encode($sign), '+/', '-_'), '=');
        return $accessKey . ':' . $encodedSign . ':' . $encodedPolicy;
    }
}
