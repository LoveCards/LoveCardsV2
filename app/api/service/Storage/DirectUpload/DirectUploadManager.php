<?php

namespace app\api\service\Storage\DirectUpload;

use app\api\service\Storage\ChannelManager;
use app\api\model\Files;

class DirectUploadManager
{
    private static array $providers = [];

    public static function getProvider(string $slug): DirectUploadProvider
    {
        if (!isset(self::$providers[$slug])) {
            $channel = ChannelManager::getBySlug($slug);
            self::$providers[$slug] = self::createProvider($slug, $channel);
        }
        return self::$providers[$slug];
    }

    private static function createProvider(string $slug, array $config): DirectUploadProvider
    {
        $type = $config['type'] ?? '';

        return match ($type) {
            'oss' => new OssDirectUpload($slug, $config),
            'cos' => new CosDirectUpload($slug, $config),
            'qiniu' => new QiniuDirectUpload($slug, $config),
            default => throw new \app\api\ApiException('不支持的直传通道: ' . $slug),
        };
    }

    public static function getDefaultProvider(): DirectUploadProvider
    {
        $default = ChannelManager::getDefaultChannel();
        return self::getProvider($default['slug']);
    }

    public static function createPendingRecord(
        string $filename,
        string $mime,
        int $size,
        string $path,
        ?int $userId = null,
        ?int $expire = null
    ): array {
        $provider = self::getDefaultProvider();

        if (!$provider->isAvailable()) {
            throw new \app\api\ApiException('默认直传通道不可用');
        }

        $expire = $expire ?? ChannelManager::getDirectUploadExpire();
        $expireAt = date('Y-m-d H:i:s', time() + $expire);

        $fileModel = new Files();
        $fileModel->channel_slug = $provider->getType();
        $fileModel->user_id = $userId;
        $fileModel->original_name = $filename;
        $fileModel->file_path = $path;
        $fileModel->file_url = '';
        $fileModel->file_size = $size;
        $fileModel->file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $fileModel->mime_type = $mime;
        $fileModel->driver_path = '';
        $fileModel->scene = 'direct';
        $fileModel->status = Files::STATUS_NORMAL;
        $fileModel->upload_status = Files::UPLOAD_PENDING;
        $fileModel->expire_at = $expireAt;
        $fileModel->created_at = date('Y-m-d H:i:s');
        $fileModel->updated_at = date('Y-m-d H:i:s');
        $fileModel->save();

        $path = $fileModel->file_path . '.' . $fileModel->file_ext;
        $credential = $provider->getUploadCredential($filename, $mime, $size, $path, $expire);

        return [
            'record_id' => $fileModel->id,
            'upload_url' => $credential['upload_url'],
            'form_data' => $credential['form_data'],
            'expire' => $credential['expire'],
        ];
    }

    public static function confirmUpload(int $recordId): bool
    {
        $fileModel = Files::find($recordId);
        if ($fileModel === null) {
            return false;
        }

        if ($fileModel->upload_status !== Files::UPLOAD_PENDING) {
            return false;
        }

        if ($fileModel->isExpired()) {
            $fileModel->markAsFailed();
            return false;
        }

        // 服务端推导 driver_path，不信任客户端
        $driverPath = $fileModel->file_path . '.' . $fileModel->file_ext;

        try {
            $provider = self::getProvider($fileModel->channel_slug);
            $url = $provider->getFileUrl($driverPath);

            $fileModel->markAsCompleted($url, $driverPath);
            return true;
        } catch (\Exception $e) {
            $fileModel->markAsFailed();
            return false;
        }
    }

    public static function cleanupExpired(int $limit = 100): array
    {
        return Files::cleanupExpired($limit);
    }
}
