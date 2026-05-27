<?php

namespace app\api\service\Sender\Contract;

use app\api\ApiException;

abstract class AbstractDriver implements SenderInterface
{
    protected array $config;
    protected string $channelSlug;
    protected string $channelType;

    public function __construct(string $slug, array $config, string $channelType)
    {
        $this->channelSlug = $slug;
        $this->config = $config;
        $this->channelType = $channelType;
    }

    public static function meta(): array
    {
        $shortName = (new \ReflectionClass(static::class))->getShortName();
        $type = strtolower(str_replace('Driver', '', $shortName));

        return [
            'type'        => $type,
            'channelType' => $type,
            'name'        => static::class,
            'icon'        => 'mdi-email',
            'fields'      => [],
        ];
    }

    public static function supportedTypes(): array
    {
        return ['code', 'notify'];
    }

    protected function renderTemplate(string $template, array $vars): string
    {
        $filename = $this->channelType . '_' . $template . '.txt';
        $path = __DIR__ . '/../template/' . $filename;
        if (!is_file($path)) {
            throw new ApiException("模板不存在: {$filename}");
        }
        $content = file_get_contents($path);
        foreach ($vars as $key => $value) {
            $content = str_replace('{' . $key . '}', (string) $value, $content);
        }
        return $content;
    }
}
