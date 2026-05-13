<?php

namespace app\api\service\Storage\Contract;

class StorageResult
{
    public int $id;
    public string $url;
    public string $path;
    public string $driverPath;
    public int $size;
    public string $mimeType;
    public string $originalName;
    public string $channelSlug;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->url = $data['url'] ?? '';
        $this->path = $data['path'] ?? '';
        $this->driverPath = $data['driver_path'] ?? '';
        $this->size = $data['size'] ?? 0;
        $this->mimeType = $data['mime_type'] ?? '';
        $this->originalName = $data['original_name'] ?? '';
        $this->channelSlug = $data['channel_slug'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'path' => $this->path,
            'size' => $this->size,
            'mime_type' => $this->mimeType,
            'original_name' => $this->originalName,
            'channel_slug' => $this->channelSlug,
        ];
    }
}