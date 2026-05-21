<?php

namespace app\api\service\Storage;

use app\api\service\Storage\Contract\HasDirectUpload;
use app\api\model\Files;

class DirectUploadManager
{
    public static function createPendingRecord(
        string $filename,
        string $mime,
        int $size,
        string $path,
        ?int $userId = null,
        ?int $expire = null
    ): array {
        $defaultChannel = ChannelManager::getDefaultChannel();
        $driver = StorageFactory::make($defaultChannel['slug']);

        if (!$driver instanceof HasDirectUpload) {
            throw new \app\api\ApiException('该渠道不支持直传');
        }

        $expire = $expire ?? ChannelManager::getDirectUploadExpire();
        $expireAt = date('Y-m-d H:i:s', time() + $expire);

        $fileModel = new Files();
        $fileModel->hash = Files::generateHash();
        $fileModel->channel_slug = $defaultChannel['slug'];
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

        $credential = $driver->getUploadCredential($filename, $mime, $size, $path, $expire);

        return [
            'record_id' => $fileModel->id,
            'upload_url' => $credential->url,
            'method' => $credential->method,
            'headers' => $credential->headers,
            'form_data' => $credential->formData,
            'expire' => $credential->expire,
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

        try {
            $driver = StorageFactory::make($fileModel->channel_slug);
            $url = $driver->getUrl($fileModel->file_path);

            $fileModel->markAsCompleted($url, $fileModel->file_path);
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
