<?php

namespace app\api\controller\admin;

use think\facade\Request;
use think\facade\Db;
use app\api\controller\BaseController;
use app\api\service\Config as ConfigService;
use app\api\service\Storage\ChannelManager;
use app\api\service\Storage\StorageFactory;
use app\api\ApiResponse;

class Config extends BaseController
{
    protected function getAllowedGroups(): array
    {
        $groups = ['core', 'upload', 'cards', 'comments', 'user', 'geetest', 'mail', 'storage'];
        foreach (StorageFactory::getRegisteredTypes() as $type) {
            $groups[] = 'storage_' . $type;
        }
        return $groups;
    }

    public function index()
    {
        $group = Request::param('group', '');
        $allowedGroups = $this->getAllowedGroups();

        if (empty($group)) {
            $result = [];
            foreach ($allowedGroups as $g) {
                $result[$g] = ConfigService::getGroup($g);
            }
            return ApiResponse::createOk($result);
        }

        $groupList = array_map('trim', explode(',', $group));
        $result = [];
        foreach ($groupList as $g) {
            if (in_array($g, $allowedGroups)) {
                $result[$g] = ConfigService::getGroup($g);
            }
        }
        return ApiResponse::createOk($result);
    }

    public function save()
    {
        $params = Request::param();
        $allowedGroups = $this->getAllowedGroups();

        foreach ($params as $group => $config) {
            if (!in_array($group, $allowedGroups)) {
                continue;
            }
            if (!is_array($config)) {
                continue;
            }
            ConfigService::setGroup($group, $config);
        }

        return ApiResponse::createNoContent();
    }

    public function storageChannels()
    {
        $result = [];
        foreach (StorageFactory::getRegisteredTypes() as $type) {
            $driverClass = StorageFactory::getDriverClass($type);
            if ($driverClass === null) {
                continue;
            }
            $meta = $driverClass::meta();
            $result[] = [
                'slug' => $type,
                'name' => $meta['name'] ?? $type,
                'icon' => $meta['icon'] ?? 'mdi-cloud',
                'fields' => $meta['fields'] ?? [],
            ];
        }
        return ApiResponse::createOk($result);
    }

    public function testChannel($channel = '')
    {
        $channel = $channel ?: Request::param('channel', '');
        if (empty($channel)) {
            return ApiResponse::createBadRequest('请指定渠道');
        }

        try {
            $config = ConfigService::getGroup('storage_' . $channel);
            if (empty($config)) {
                return ApiResponse::createOk(['success' => false, 'message' => '渠道配置不存在']);
            }

            $type = $config['type'] ?? $channel;

            switch ($type) {
                case 'local':
                    $root = $config['root'] ?? 'public/storage';
                    $fullPath = app()->getRootPath() . $root;
                    if (!is_dir($fullPath)) {
                        @mkdir($fullPath, 0755, true);
                    }
                    $writable = is_writable($fullPath);
                    return ApiResponse::createOk([
                        'success' => $writable,
                        'message' => $writable ? '目录可写' : '目录不可写: ' . $fullPath,
                    ]);

                case 'cos':
                    return ApiResponse::createOk($this->testCos($config));

                case 'oss':
                    return ApiResponse::createOk($this->testOss($config));

                case 'qiniu':
                    return ApiResponse::createOk($this->testQiniu($config));

                default:
                    return ApiResponse::createOk(['success' => false, 'message' => '不支持的渠道类型']);
            }
        } catch (\Exception $e) {
            return ApiResponse::createOk(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function testCos(array $config): array
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

    private function testOss(array $config): array
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

    private function testQiniu(array $config): array
    {
        $accessKey = $config['access_key'] ?? '';
        $secretKey = $config['secret_key'] ?? '';
        $bucket = $config['bucket'] ?? '';

        if (empty($accessKey) || empty($secretKey) || empty($bucket)) {
            return ['success' => false, 'message' => '配置不完整'];
        }

        try {
            $url = "https://rs.qiniu.com/mgr?bucket={$bucket}";

            $token = $this->getQiniuToken($accessKey, $secretKey, $bucket, '/mgr');

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

    private function getQiniuToken($accessKey, $secretKey, $bucket, $path): string
    {
        $expire = time() + 3600;
        $policy = json_encode(['scope' => $bucket, 'deadline' => $expire]);
        $encodedPolicy = rtrim(strtr(base64_encode($policy), '+/', '-_'), '=');
        $sign = hash_hmac('sha1', $encodedPolicy, $secretKey, true);
        $encodedSign = rtrim(strtr(base64_encode($sign), '+/', '-_'), '=');
        return $accessKey . ':' . $encodedSign . ':' . $encodedPolicy;
    }

    public function channelStats()
    {
        $channels = StorageFactory::getRegisteredTypes();
        $result = [];

        foreach ($channels as $slug) {
            $count = Db::table('files')
                ->where('channel_slug', $slug)
                ->whereNull('deleted_at')
                ->count();

            $totalSize = Db::table('files')
                ->where('channel_slug', $slug)
                ->whereNull('deleted_at')
                ->sum('file_size');

            $result[$slug] = [
                'file_count' => $count ?? 0,
                'total_size' => $totalSize ?? 0,
            ];
        }

        return ApiResponse::createOk($result);
    }
}
