<?php

namespace app\api\service\Storage\DirectUpload;

use app\api\service\Storage\ChannelManager;

abstract class AbstractDirectUpload implements DirectUploadProvider
{
    protected array $config;
    protected string $slug;

    public function __construct(string $slug, array $config)
    {
        $this->slug = $slug;
        $this->config = $config;
    }

    public function getType(): string
    {
        return $this->config['type'] ?? 'cloud';
    }

    public function isAvailable(): bool
    {
        return ChannelManager::isAvailable($this->slug);
    }

    public function getUploadUrl(): string
    {
        return '';
    }

    public function confirmUpload(string $driverPath): bool
    {
        return true;
    }

    protected function buildFormData(string $key, int $expire): array
    {
        return [
            'key' => $key,
            'expire' => $expire,
            'token' => '',
        ];
    }

    protected function getExpireTime(int $expire): string
    {
        return date('Y-m-d\TH:i:s\Z', time() + $expire);
    }

    protected function getAllowedMimeTypes(): array
    {
        $types = $this->config['allow_mime_types'] ?? '';
        if (empty($types)) {
            return [];
        }
        return array_map('trim', explode(',', $types));
    }

    protected function isMimeAllowed(string $mime): bool
    {
        $allowed = $this->getAllowedMimeTypes();
        if (empty($allowed)) {
            return true;
        }

        foreach ($allowed as $pattern) {
            if ($pattern === $mime) {
                return true;
            }
            if (str_ends_with($pattern, '/*')) {
                $prefix = rtrim($pattern, '/*');
                if (str_starts_with($mime, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }
}