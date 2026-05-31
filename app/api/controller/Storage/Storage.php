<?php

namespace app\api\controller\Storage;

use think\facade\Request;

use app\common\service\Config as ConfigService;
use app\api\service\Storage\StorageFactory;
use app\api\service\Storage\StorageManager;
use app\api\service\Storage\ChannelTester;
use app\api\ApiResponse;
use app\api\controller\BaseController;

class Storage extends BaseController
{
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
                'type'        => StorageFactory::mapMetaType($field['type'] ?? 'text'),
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
                    'type'        => StorageFactory::mapMetaType($field['type'] ?? 'text'),
                    'default'     => $field['default'] ?? '',
                    'description' => $field['label'] ?? $field['key'],
                ];
            }

            if (!empty($schema)) {
                $results[] = ConfigService::register('storage_' . $type, $schema);
            }
        }

        $settings = [
            'default'              => ['type' => 'string', 'default' => 'local', 'description' => '默认存储渠道'],
            'rate_limit_max'       => ['type' => 'int',    'default' => 10,     'description' => '限流次数'],
            'rate_limit_window'    => ['type' => 'int',    'default' => 60,     'description' => '限流窗口(秒)'],
            'direct_upload_expire' => ['type' => 'int',    'default' => 3600,   'description' => '直传过期时间(秒)'],
        ];
        $results[] = ConfigService::register('storage', $settings);

        return ApiResponse::createOk($results);
    }

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

    public function channels()
    {
        $result = [];
        foreach (StorageFactory::getRegisteredTypes() as $type) {
            $driverClass = StorageFactory::getDriverClass($type);
            if ($driverClass === null) {
                continue;
            }
            $meta = $driverClass::meta();
            $result[] = [
                'slug'   => $type,
                'name'   => $meta['name'] ?? $type,
                'icon'   => $meta['icon'] ?? 'mdi-cloud',
                'fields' => $meta['fields'] ?? [],
            ];
        }
        return ApiResponse::createOk($result);
    }

    public function testChannel($channel = '')
    {
        $channel = $channel ?: Request::param('channel', '');
        if (empty($channel)) {
            return ApiResponse::createBadRequest('请指定渠道');
        }

        try {
            $result = ChannelTester::test($channel);
            return ApiResponse::createOk($result);
        } catch (\Exception $e) {
            return ApiResponse::createOk(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function channelStats()
    {
        return ApiResponse::createOk(StorageManager::channelStats());
    }
}
