<?php

namespace app\api\controller;

use think\facade\Request;

use app\api\service\Config as ConfigService;
use app\api\service\Storage\StorageFactory;
use app\api\ApiResponse;

class Storage extends BaseController
{
    /**
     * 读取指定 Driver 的 meta，转换为配置 schema 格式
     * GET /api/storage/{type}/meta
     */
    public function meta(string $type)
    {
        $driverClass = StorageFactory::getDriverClass($type);
        if (!$driverClass) {
            return ApiResponse::createBadRequest('驱动不存在: ' . $type);
        }

        $meta = $driverClass::meta();

        $schema = [];
        foreach ($meta['fields'] as $field) {
            $schema[$field['key']] = [
                'type'        => self::mapMetaType($field['type'] ?? 'text'),
                'default'     => $field['default'] ?? '',
                'description' => $field['label'] ?? $field['key'],
            ];
        }

        return ApiResponse::createOk([
            'type'   => $type,
            'name'   => $meta['name'] ?? $type,
            'icon'   => $meta['icon'] ?? '',
            'schema' => $schema,
            'group'  => 'storage_' . $type,
        ]);
    }

    /**
     * 扫描所有 Driver，注册配置 + seed SQL
     * POST /api/storage/install
     */
    public function install()
    {
        $types = StorageFactory::getRegisteredTypes();
        $results = [];

        foreach ($types as $type) {
            $driverClass = StorageFactory::getDriverClass($type);
            if (!$driverClass) continue;

            $meta = $driverClass::meta();

            $schema = [];
            foreach ($meta['fields'] as $field) {
                $schema[$field['key']] = [
                    'type'        => self::mapMetaType($field['type'] ?? 'text'),
                    'default'     => $field['default'] ?? '',
                    'description' => $field['label'] ?? $field['key'],
                ];
            }

            if (!empty($schema)) {
                $results[] = ConfigService::register('storage_' . $type, $schema);
            }
        }

        // 注册全局存储设置
        $settings = [
            'default'              => ['type' => 'string', 'default' => 'local', 'description' => '默认存储渠道'],
            'rate_limit_max'       => ['type' => 'int',    'default' => 10,     'description' => '限流次数'],
            'rate_limit_window'    => ['type' => 'int',    'default' => 60,     'description' => '限流窗口(秒)'],
            'direct_upload_expire' => ['type' => 'int',    'default' => 3600,   'description' => '直传过期时间(秒)'],
        ];
        $results[] = ConfigService::register('storage', $settings);

        return ApiResponse::createOk($results);
    }

    /**
     * 列出所有已注册的 Driver 类型
     * GET /api/storage/types
     */
    public function types()
    {
        $types = StorageFactory::getRegisteredTypes();
        $result = [];

        foreach ($types as $type) {
            $driverClass = StorageFactory::getDriverClass($type);
            $meta = $driverClass ? $driverClass::meta() : [];
            $result[] = [
                'type' => $type,
                'name' => $meta['name'] ?? $type,
                'icon' => $meta['icon'] ?? 'mdi-cloud',
            ];
        }

        return ApiResponse::createOk($result);
    }

    /**
     * UI input type → schema 数据类型映射
     */
    private static function mapMetaType(string $uiType): string
    {
        return match ($uiType) {
            'text', 'password', 'select', 'textarea' => 'string',
            'number'                                  => 'int',
            'checkbox', 'toggle', 'switch'            => 'bool',
            'json'                                    => 'json',
            default                                   => 'string',
        };
    }
}
