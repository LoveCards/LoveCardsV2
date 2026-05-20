<?php

namespace app\api\controller;

use think\facade\Request;

use app\api\service\Config as ConfigService;
use app\api\service\Storage\ChannelManager;
use app\api\service\Storage\StorageFactory;
use app\api\service\Storage\ChannelTester;

use app\api\ApiResponse;

class Config extends BaseController
{
    protected function getAllowedGroups(): array
    {
        $groups = ['core', 'upload', 'cards', 'comments', 'user', 'geetest', 'mail', 'storage'];
        foreach (StorageFactory::getRegisteredTypes() as $type) {
            $groups[] = 'storage_' . $type;
        }
        return $groups;
    }

    public function index()
    {
        $group = Request::param('group', '');
        $allowedGroups = $this->getAllowedGroups();

        if (empty($group)) {
            $result = [];
            foreach ($allowedGroups as $g) {
                $result[$g] = ConfigService::getGroup($g);
            }
            return ApiResponse::createOk($result);
        }

        $groupList = array_map('trim', explode(',', $group));
        $result = [];
        foreach ($groupList as $g) {
            if (in_array($g, $allowedGroups)) {
                $result[$g] = ConfigService::getGroup($g);
            }
        }
        return ApiResponse::createOk($result);
    }

    public function save()
    {
        $params = Request::param();
        $allowedGroups = $this->getAllowedGroups();

        foreach ($params as $group => $config) {
            if (!in_array($group, $allowedGroups)) {
                continue;
            }
            if (!is_array($config)) {
                continue;
            }
            ConfigService::setGroup($group, $config);
        }

        return ApiResponse::createNoContent();
    }

    public function storageChannels()
    {
        $result = [];
        foreach (StorageFactory::getRegisteredTypes() as $type) {
            $driverClass = StorageFactory::getDriverClass($type);
            if ($driverClass === null) {
                continue;
            }
            $meta = $driverClass::meta();
            $result[] = [
                'slug' => $type,
                'name' => $meta['name'] ?? $type,
                'icon' => $meta['icon'] ?? 'mdi-cloud',
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
        $channels = StorageFactory::getRegisteredTypes();
        $result = [];

        foreach ($channels as $slug) {
            $count = \think\facade\Db::table('files')
                ->where('channel_slug', $slug)
                ->whereNull('deleted_at')
                ->count();

            $totalSize = \think\facade\Db::table('files')
                ->where('channel_slug', $slug)
                ->whereNull('deleted_at')
                ->sum('file_size');

            $result[$slug] = [
                'file_count' => $count ?? 0,
                'total_size' => $totalSize ?? 0,
            ];
        }

        return ApiResponse::createOk($result);
    }
}
