<?php

namespace app\api\service\Storage;

use think\file\UploadedFile;
use think\facade\Db;
use app\api\model\Files;
use app\api\service\Storage\Contract\StorageResult;
use yunarch\utils\src\ModelList;

class StorageManager
{
    public static function upload(UploadedFile $file, string $path, array $options = []): StorageResult
    {
        $defaultChannel = ChannelManager::getDefaultChannel();
        $driver = StorageFactory::make($defaultChannel['slug']);

        $mime = $driver->detectMimeType($file->getPathname());

        $allowedMimes = $driver->supportedMimeTypes();
        if (!empty($allowedMimes) && !in_array($mime, $allowedMimes)) {
            throw new \app\api\ApiException('不支持的文件类型: ' . $mime);
        }

        $maxSize = $driver->maxFileSize();
        if ($maxSize > 0 && $file->getSize() > $maxSize) {
            throw new \app\api\ApiException('文件大小超出限制');
        }

        $result = $driver->doUpload($file, $path);

        $fileRecord = Files::create([
            'channel_slug' => $defaultChannel['slug'],
            'user_id' => $options['user_id'] ?? null,
            'is_public' => $options['is_public'] ?? 0,
            'scene' => $options['scene'] ?? 'direct',
            'ref_type' => $options['ref_type'] ?? null,
            'ref_id' => $options['ref_id'] ?? null,
            'original_name' => $file->getOriginalName(),
            'file_path' => $result['path'],
            'file_url' => $result['url'],
            'file_size' => $file->getSize(),
            'file_ext' => strtolower(pathinfo($file->getOriginalName(), PATHINFO_EXTENSION)),
            'mime_type' => $mime,
            'driver_path' => $result['driver_path'],
            'status' => $options['status'] ?? Files::STATUS_NORMAL,
            'upload_status' => $options['upload_status'] ?? Files::UPLOAD_COMPLETED,
        ]);

        return new StorageResult([
            'id' => $fileRecord->id,
            'url' => $result['url'],
            'path' => $result['path'],
            'driver_path' => $result['driver_path'],
            'size' => $file->getSize(),
            'mime_type' => $mime,
            'original_name' => $file->getOriginalName(),
            'channel_slug' => $defaultChannel['slug'],
        ]);
    }

    public static function channel(string $slug): ChannelUploader
    {
        return new ChannelUploader($slug);
    }

    public static function list(array $params, int $userId = -1, bool $isAdmin = false): array
    {
        $params['search_default_key'] = 'original_name';

        $modelList = ModelList::make(Files::class);

        // 管理员查看回收站：只返回已删除的记录
        $showDeleted = !empty($params['show_deleted']);
        if ($isAdmin && $showDeleted) {
            $modelList->onlyTrashed();
        }

        $where = $params['where'] ?? [];

        if (!$isAdmin) {
            // 普通用户：只看自己或公开的（SoftDelete 框架自动过滤 deleted_at）
            if ($userId > 0) {
                $where[] = function ($q) use ($userId) {
                    $q->where('user_id', $userId)->whereOr('is_public', 1);
                };
            }
        }

        if (isset($params['scene'])) {
            $where[] = ['scene', '=', $params['scene']];
        }

        if (isset($params['ref_type'])) {
            $where[] = ['ref_type', '=', $params['ref_type']];
        }

        if (isset($params['ref_id'])) {
            $where[] = ['ref_id', '=', $params['ref_id']];
        }

        if (isset($params['status'])) {
            $where[] = ['status', '=', $params['status']];
        }

        $params['where'] = $where;

        $result = $modelList->getPaginate($params);
        return $result->toArray();
    }

    public static function getFile(int $fileId, int $userId = -1, bool $isAdmin = false): ?array
    {
        $query = Files::withTrashed()->where('id', $fileId);

        if (!$isAdmin) {
            $query->whereNull('deleted_at');
            if ($userId > 0) {
                $query->visible($userId);
            }
        }

        $file = $query->find();
        return $file ? $file->toArray() : null;
    }

    public static function batchOperate(string $method, array $ids): void
    {
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, fn($id) => $id > 0);
        if (empty($ids)) return;

        switch ($method) {
            case 'approve':
                Db::table('files')->whereIn('id', $ids)->update(['status' => Files::STATUS_NORMAL]);
                break;
            case 'ban':
                Db::table('files')->whereIn('id', $ids)->update(['status' => Files::STATUS_BANNED]);
                break;
            case 'toggle_public':
                Db::table('files')->whereIn('id', $ids)->update(['is_public' => Db::raw('1 - is_public')]);
                break;
            case 'trash':
                foreach ($ids as $id) {
                    $file = Files::find($id);
                    if ($file) $file->delete();
                }
                break;
            case 'restore':
                foreach ($ids as $id) {
                    $file = Files::withTrashed()->find($id);
                    if ($file) $file->restore();
                }
                break;
            case 'hard_delete':
                foreach ($ids as $id) {
                    self::hardDelete($id);
                }
                break;
            default:
                throw new \app\api\ApiException('不支持的操作');
        }
    }

    public static function hardDelete(int $fileId): bool
    {
        $file = Files::withTrashed()->find($fileId);
        if (!$file) return false;

        try {
            $driver = StorageFactory::make($file->channel_slug);
            $driver->delete($file->driver_path);
        } catch (\Throwable $e) {
        }

        Db::table('files')->where('id', $fileId)->delete();
        return true;
    }

    public static function delete(int $fileId): bool
    {
        $file = Files::find($fileId);
        if ($file === null) {
            return false;
        }

        if (empty($file->driver_path)) {
            return false;
        }

        $driver = StorageFactory::make($file->channel_slug);
        return $driver->delete($file->driver_path);
    }

    public static function checkRateLimit(string $uid): bool
    {
        $settings = ChannelManager::getRateLimitSettings();
        $max = $settings['max'];
        $window = $settings['window'];

        $key = 'rate_limit_upload_' . $uid;
        $timestamps = cache($key) ?? [];

        $now = time();
        $timestamps = array_filter($timestamps, fn($t) => $t > $now - $window);

        if (count($timestamps) >= $max) {
            return false;
        }

        $timestamps[] = $now;
        cache($key, array_values($timestamps), $window);

        return true;
    }
}

class ChannelUploader
{
    private string $slug;

    public function __construct(string $slug)
    {
        $this->slug = $slug;
    }

    public function upload(UploadedFile $file, string $path): StorageResult
    {
        $driver = StorageFactory::make($this->slug);
        return $driver->upload($file, $path);
    }
}
