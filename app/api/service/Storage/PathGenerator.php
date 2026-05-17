<?php

namespace app\api\service\Storage;

class PathGenerator
{
    private const DEFAULT_TEMPLATE = 'storage/{date}/{uuid}.{ext}';

    public static function generate(array $channelConfig, string $originalFilename): string
    {
        $template = $channelConfig['path_template'] ?? self::DEFAULT_TEMPLATE;
        $ext = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));

        $replacements = [
            '{date}' => date('Ymd'),
            '{uuid}' => self::uuid(),
            '{ext}'  => $ext,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    private static function uuid(): string
    {
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
