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
        $groups = ConfigService::getSchemaGroups();
        if (empty($groups)) {
            $groups = ['core', 'upload', 'cards', 'comments', 'user', 'geetest', 'version'];
        }
        return $groups;
    }

    // ═══ 读取 ═══

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

    public function groups()
    {
        return ApiResponse::createOk(ConfigService::getSchemaGroups());
    }

    // ═══ 写入 ═══

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

    // ═══ 初始化/注册 ═══

    /**
     * 初始化配置系统
     * 扫描 config/apps/*.php 并批量注册 + seed SQL
     * POST /api/config/init
     */
    public function init()
    {
        $result = ConfigService::init();
        return ApiResponse::createOk($result);
    }

    /**
     * 注册单个 group 的 schema
     * POST /api/config/register
     * Body: { "group": "storage_cos", "schema": {...} }
     */
    public function register()
    {
        $group = Request::param('group', '');
        $schema = Request::param('schema', []);

        if (empty($group) || empty($schema)) {
            return ApiResponse::createBadRequest('缺少 group 或 schema');
        }

        $result = ConfigService::register($group, $schema);
        return ApiResponse::createOk($result);
    }

    // ═══ 管理 ═══

    /**
     * 重载配置缓存
     * POST /api/config/reload
     */
    public function reload()
    {
        $group = Request::param('group');
        ConfigService::reload($group ?: null);
        return ApiResponse::createNoContent();
    }

    // ═══ Storage 相关（保留兼容） ═══

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
