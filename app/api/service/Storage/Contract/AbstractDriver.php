<?php

namespace app\api\service\Storage\Contract;

use think\file\UploadedFile;
use app\api\ApiException;

abstract class AbstractDriver implements DriverInterface
{
    protected array $config;
    protected string $channelSlug;

    public function __construct(string $slug, array $config)
    {
        $this->channelSlug = $slug;
        $this->config = $config;
    }

    public static function meta(): array
    {
        return [
            'name' => static::class,
            'icon' => 'mdi-cloud',
            'fields' => [],
        ];
    }

    protected function validateFile(UploadedFile $file): void
    {
        $mime = $this->detectMime($file->getPathname());
        $this->checkMime($mime);
        $this->checkSize($file->getSize());
    }

    protected function validateDirectUpload(string $mime, int $size): void
    {
        $this->checkMime($mime);
        $this->checkSize($size);
    }

    protected function checkMime(string $mime): void
    {
        $allowed = $this->getAllowedMimeTypes();
        if (empty($allowed)) {
            return;
        }

        foreach ($allowed as $pattern) {
            if ($pattern === $mime) {
                return;
            }
            if (str_ends_with($pattern, '/*')) {
                $prefix = rtrim($pattern, '/*');
                if (str_starts_with($mime, $prefix)) {
                    return;
                }
            }
        }

        throw new ApiException('不支持的文件类型: ' . $mime);
    }

    protected function checkSize(int $size): void
    {
        $maxSize = (int) ($this->config['max_file_size'] ?? 0);
        if ($maxSize > 0 && $size > $maxSize) {
            throw new ApiException('文件大小超出限制');
        }
    }

    protected function getAllowedMimeTypes(): array
    {
        $types = $this->config['allow_mime_types'] ?? '';
        if (empty($types)) {
            return [];
        }
        return array_map('trim', explode(',', $types));
    }

    protected function detectMime(string $pathname): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $pathname);
            finfo_close($finfo);
            return $mime ?: 'application/octet-stream';
        }
        return mime_content_type($pathname) ?: 'application/octet-stream';
    }

    protected function buildStorageResult(array $data): StorageResult
    {
        return new StorageResult($data);
    }
}
