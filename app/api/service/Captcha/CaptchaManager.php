<?php

namespace app\api\service\Captcha;

use app\common\service\Config as ConfigService;

class CaptchaManager
{
    public static function types(): array
    {
        $result = [];
        foreach (CaptchaFactory::getRegisteredSlugs() as $slug) {
            $driverClass = CaptchaFactory::getDriverClass($slug);
            $meta = $driverClass ? $driverClass::meta() : [];
            $result[] = [
                'slug' => $slug,
                'type' => $meta['type'] ?? $slug,
                'name' => $meta['name'] ?? $slug,
                'icon' => $meta['icon'] ?? 'mdi-shield-check-outline',
            ];
        }
        return $result;
    }

    public static function drivers(): array
    {
        $result = [];
        foreach (CaptchaFactory::getRegisteredSlugs() as $slug) {
            $driverClass = CaptchaFactory::getDriverClass($slug);
            if ($driverClass === null) continue;
            $meta = $driverClass::meta();
            $result[] = [
                'slug'   => $slug,
                'type'   => $meta['type'] ?? $slug,
                'name'   => $meta['name'] ?? $slug,
                'icon'   => $meta['icon'] ?? '',
                'fields' => $meta['fields'] ?? [],
            ];
        }
        return $result;
    }

    public static function meta(string $slug): array
    {
        $driverClass = CaptchaFactory::getDriverClass($slug);
        if (!$driverClass) {
            throw new \app\api\ApiException('驱动不存在: ' . $slug);
        }

        $meta   = $driverClass::meta();
        $schema = [];
        foreach ($meta['fields'] as $field) {
            $schema[$field['key']] = [
                'type'        => CaptchaFactory::mapMetaType($field['type'] ?? 'text'),
                'default'     => $field['default'] ?? '',
                'description' => $field['label'] ?? $field['key'],
            ];
        }

        return [
            'slug'   => $slug,
            'type'   => $meta['type'] ?? $slug,
            'name'   => $meta['name'] ?? $slug,
            'icon'   => $meta['icon'] ?? '',
            'schema' => $schema,
            'group'  => 'captcha_' . $slug,
        ];
    }

    public static function install(): array
    {
        $results = [];

        foreach (CaptchaFactory::getRegisteredSlugs() as $slug) {
            $driverClass = CaptchaFactory::getDriverClass($slug);
            if (!$driverClass) continue;

            $meta   = $driverClass::meta();
            $schema = [];
            foreach ($meta['fields'] as $field) {
                $schema[$field['key']] = [
                    'type'        => CaptchaFactory::mapMetaType($field['type'] ?? 'text'),
                    'default'     => $field['default'] ?? '',
                    'description' => $field['label'] ?? $field['key'],
                ];
            }
            if (!empty($schema)) {
                $results[] = ConfigService::register('captcha_' . $slug, $schema);
            }
        }

        return $results;
    }

    public static function config(): array
    {
        return [
            'captcha_geetest_v4' => [
                'id'     => ConfigService::get('captcha_geetest_v4.id', ''),
                'status' => ConfigService::get('captcha_geetest_v4.status', false),
            ],
            'code_enabled'    => ConfigService::get('captcha.code_enabled', true),
            'captcha_enabled' => ConfigService::get('captcha.captcha_enabled', true),
        ];
    }
}
