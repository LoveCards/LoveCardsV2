<?php

namespace app\api\controller;

use think\facade\Request;

use app\api\service\Config as ConfigService;

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

    public function update()
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

            $schema = ConfigService::getSchema($group);
            $validated = [];
            foreach ($config as $key => $value) {
                if (isset($schema[$key])) {
                    $validated[$key] = $value;
                }
            }

            if (!empty($validated)) {
                ConfigService::setGroup($group, $validated);
            }
        }

        return ApiResponse::createNoContent();
    }

    public function init()
    {
        $result = ConfigService::init();
        return ApiResponse::createOk($result);
    }

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

    public function reload()
    {
        $group = Request::param('group');
        ConfigService::reload($group ?: null);
        return ApiResponse::createNoContent();
    }
}
