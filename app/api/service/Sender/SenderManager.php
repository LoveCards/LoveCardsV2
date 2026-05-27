<?php

namespace app\api\service\Sender;

use app\api\service\Sender\Contract\Message;
use app\api\service\System\Config as ConfigService;

class SenderManager
{
    public static function types(): array
    {
        $result = [];
        foreach (SenderFactory::getRegisteredTypes() as $type) {
            $driverClass = SenderFactory::getDriverClass($type);
            $meta = $driverClass ? $driverClass::meta() : [];
            $result[] = [
                'type'        => $type,
                'channelType' => $meta['channelType'] ?? $type,
                'name'        => $meta['name'] ?? $type,
                'icon'        => $meta['icon'] ?? 'mdi-email',
                'supportedTypes' => $driverClass ? $driverClass::supportedTypes() : [],
            ];
        }
        return $result;
    }

    public static function meta(string $type): array
    {
        $driverClass = SenderFactory::getDriverClass($type);
        if (!$driverClass) {
            throw new \app\api\ApiException('驱动不存在: ' . $type);
        }

        $meta = $driverClass::meta();
        $schema = [];
        foreach ($meta['fields'] as $field) {
            $schema[$field['key']] = [
                'type'        => SenderFactory::mapMetaType($field['type'] ?? 'text'),
                'default'     => $field['default'] ?? '',
                'description' => $field['label'] ?? $field['key'],
            ];
        }

        return [
            'type'           => $type,
            'channelType'    => $meta['channelType'] ?? $type,
            'name'           => $meta['name'] ?? $type,
            'icon'           => $meta['icon'] ?? '',
            'schema'         => $schema,
            'group'          => 'sender_' . $type,
            'supportedTypes' => $driverClass::supportedTypes(),
        ];
    }

    public static function channels(): array
    {
        $result = [];
        foreach (SenderFactory::getRegisteredTypes() as $type) {
            $driverClass = SenderFactory::getDriverClass($type);
            if ($driverClass === null) continue;
            $meta = $driverClass::meta();
            $result[] = [
                'slug'           => $type,
                'channelType'    => $meta['channelType'] ?? $type,
                'name'           => $meta['name'] ?? $type,
                'icon'           => $meta['icon'] ?? 'mdi-email',
                'fields'         => $meta['fields'] ?? [],
                'supportedTypes' => $driverClass::supportedTypes(),
            ];
        }
        return $result;
    }

    public static function install(): array
    {
        $results = [];

        foreach (SenderFactory::getRegisteredTypes() as $type) {
            $driverClass = SenderFactory::getDriverClass($type);
            if (!$driverClass) continue;

            $meta = $driverClass::meta();
            $schema = [];
            foreach ($meta['fields'] as $field) {
                $schema[$field['key']] = [
                    'type'        => SenderFactory::mapMetaType($field['type'] ?? 'text'),
                    'default'     => $field['default'] ?? '',
                    'description' => $field['label'] ?? $field['key'],
                ];
            }
            if (!empty($schema)) {
                $results[] = ConfigService::register('sender_' . $type, $schema);
            }
        }

        return $results;
    }

    public static function testChannel(string $slug, string $to = 'test@example.com'): array
    {
        try {
            $driverClass = SenderFactory::getDriverClass($slug);
            if (!$driverClass) {
                $driver = SenderFactory::make($slug);
                $meta = $driver::meta();
            } else {
                $meta = $driverClass::meta();
            }
            $channelType = $meta['channelType'] ?? $slug;

            $result = Sender::code($channelType, $to, '123456', 5, $slug);
            return ['success' => $result->success, 'message' => $result->error ?? '发送成功'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function templates(): array
    {
        $dir = __DIR__ . '/template/';
        if (!is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '*.txt');
        $result = [];
        foreach ($files as $file) {
            $name = basename($file, '.txt');
            $parts = explode('_', $name, 2);
            $result[] = [
                'name'        => $name,
                'channelType' => $parts[0] ?? '',
                'scene'       => $parts[1] ?? $name,
            ];
        }
        return $result;
    }
}
