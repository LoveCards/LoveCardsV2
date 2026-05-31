<?php

namespace app\api\controller\Theme;

use think\facade\Request;
use app\api\ApiException;
use app\api\ApiResponse;
use app\api\controller\BaseController;
use app\frontend\service\ThemeEngine;

class ThemeManager extends BaseController
{
    /**
     * 列出已安装主题
     */
    public function list()
    {
        return ApiResponse::createOk(ThemeEngine::listThemes());
    }

    /**
     * 上传主题 ZIP
     */
    public function upload()
    {
        $file = request()->file('file');

        if (!$file) {
            throw ApiException::badRequest('请上传 ZIP 文件');
        }

        $info = $file->move(runtime_path() . 'theme_upload');
        if (!$info) {
            throw ApiException::badRequest('文件上传失败');
        }

        $result = ThemeEngine::installTheme($info->getRealPath());
        return ApiResponse::createOk($result);
    }

    /**
     * 切换活跃主题
     */
    public function activate()
    {
        $name = Request::param('name', '');
        if (empty($name)) {
            throw ApiException::badRequest('缺少主题名称');
        }

        ThemeEngine::activateTheme($name);
        return ApiResponse::createNoContent();
    }

    /**
     * 获取当前主题配置
     */
    public function config()
    {
        $theme = ThemeEngine::getActive();
        if (empty($theme)) {
            throw ApiException::badRequest('未找到活跃主题');
        }

        $schema = $theme['config'] ?? [];
        $values = ThemeEngine::getThemeConfig($theme['name']);

        return ApiResponse::createOk([
            'name'          => $theme['name'],
            'mode'          => $theme['mode'] ?? 'spa',
            'config_schema' => $schema,
            'config_values' => $values,
        ]);
    }

    /**
     * 更新主题配置
     */
    public function updateConfig()
    {
        $params = Request::param();
        if (empty($params)) {
            throw ApiException::badRequest('缺少配置参数');
        }

        ThemeEngine::updateThemeConfig($params);
        return ApiResponse::createNoContent();
    }

    /**
     * 固化配置到 theme.json
     */
    public function freezeConfig()
    {
        ThemeEngine::freezeConfig();
        return ApiResponse::createNoContent();
    }

    /**
     * 删除主题
     */
    public function delete()
    {
        $name = Request::param('name', '');
        if (empty($name)) {
            throw ApiException::badRequest('缺少主题名称');
        }

        ThemeEngine::deleteTheme($name);
        return ApiResponse::createNoContent();
    }
}
