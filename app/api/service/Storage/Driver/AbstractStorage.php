<?php

namespace app\api\service\Storage\Driver;

use think\file\UploadedFile;
use app\api\service\Storage\Contract\StorageInterface;
use app\api\service\Storage\Contract\StorageResult;

abstract class AbstractStorage implements StorageInterface
{
    protected array $config = [];
    protected string $channelSlug = '';

    public function __construct(string $slug, array $config)
    {
        $this->channelSlug = $slug;
        $this->config = $config;
    }

    abstract public function getType(): string;

    abstract public function doUpload(UploadedFile $file, string $path): array;

    abstract public function doDelete(string $driverPath): bool;

    abstract public function getUrl(string $driverPath): string;

    public function upload(UploadedFile $file, string $path): StorageResult
    {
        $originalName = $file->getOriginalName();
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $mime = $this->detectMimeType($file->getPathname());

        $allowedMimes = $this->supportedMimeTypes();
        if (!empty($allowedMimes) && !in_array($mime, $allowedMimes)) {
            throw new \app\api\ApiException('不支持的文件类型: ' . $mime);
        }

        $maxSize = $this->maxFileSize();
        if ($maxSize > 0 && $file->getSize() > $maxSize) {
            throw new \app\api\ApiException('文件大小超出限制');
        }

        $result = $this->doUpload($file, $path);

        $fileRecord = $this->createFileRecord([
            'original_name' => $file->getOriginalName(),
            'file_path' => $result['path'],
            'file_url' => $result['url'],
            'file_size' => $file->getSize(),
            'file_ext' => $ext,
            'mime_type' => $mime,
            'driver_path' => $result['driver_path'],
        ]);

        return new StorageResult([
            'id' => $fileRecord->id,
            'url' => $result['url'],
            'path' => $result['path'],
            'driver_path' => $result['driver_path'],
            'size' => $file->getSize(),
            'mime_type' => $mime,
            'original_name' => $file->getOriginalName(),
            'channel_slug' => $this->channelSlug,
        ]);
    }

    public function delete(string $driverPath): bool
    {
        $result = $this->doDelete($driverPath);

        if ($result) {
            \app\api\model\Files::where('driver_path', $driverPath)->delete();
        }

        return $result;
    }

    public function exists(string $driverPath): bool
    {
        return \app\api\model\Files::where('driver_path', $driverPath)
            ->where('status', \app\api\model\Files::STATUS_NORMAL)
            ->find() !== null;
    }

    public function supportedMimeTypes(): array
    {
        $types = $this->config['allow_mime_types'] ?? '';
        if (empty($types)) {
            return [];
        }
        return array_map('trim', explode(',', $types));
    }

    public function detectMimeType(string $path): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $path);
            finfo_close($finfo);
            return $mime ?: 'application/octet-stream';
        }
        return mime_content_type($path) ?: 'application/octet-stream';
    }

    public function maxFileSize(): int
    {
        return (int) ($this->config['max_file_size'] ?? 0);
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    protected function createFileRecord(array $data): \app\api\model\Files
    {
        $model = new \app\api\model\Files();
        $model->channel_slug = $this->channelSlug;
        $model->original_name = $data['original_name'];
        $model->file_path = $data['file_path'];
        $model->file_url = $data['file_url'];
        $model->file_size = $data['file_size'];
        $model->file_ext = $data['file_ext'];
        $model->mime_type = $data['mime_type'];
        $model->driver_path = $data['driver_path'];
        $model->created_at = date('Y-m-d H:i:s');
        $model->updated_at = date('Y-m-d H:i:s');
        $model->save();

        return $model;
    }
}