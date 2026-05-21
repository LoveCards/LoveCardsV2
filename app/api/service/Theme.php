<?php

namespace app\api\service;

use think\Facade;
use think\facade\View;
use think\facade\Config as ThinkConfig;
use app\api\service\Config as ConfigService;

class Theme extends Facade
{
    protected static function getThemeDirectory(): array
    {
        $name = ConfigService::get('core.theme_directory', 'index');
        $path = 'theme/' . $name;
        if (!is_dir($path)) {
            $name = 'index';
            $path = 'theme/index';
        }
        return ['P' => $path, 'N' => $name];
    }

    protected static function coverThemeConfig(string $themeDir, array $data): bool
    {
        $filePath = '../public/theme/' . $themeDir . '/config.php';
        $content = file_get_contents($filePath);
        $env = 'ThemeConfig';

        foreach ($data as $key => $value) {
            $pattern = "/env\('" . $env . "\." . $key . "',\s*([^']*)\)/";
            $replacement = "env('" . $env . "." . $key . "', " . $value . ")";

            if (substr($key, 0, 4) === "Text") {
                $pattern = "/env\('" . $env . "\." . $key . "',\s*'([^']*)'\)/";
                $value = urlencode($value);
                $replacement = "env('" . $env . "." . $key . "', '" . $value . "')";
            }

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            }
        }

        try {
            file_put_contents($filePath, $content);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected static function getThemeConfig(string $themeDir, bool $original = false)
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/theme/' . $themeDir . '/config.php';
        if (!is_file($path)) {
            return false;
        }

        $config = include $path;
        $result = [];

        if ($original) {
            return $config;
        }

        if (array_key_exists('Select', $config)) {
            foreach ($config['Select'] as $key => $value) {
                $result[$key] = $value['Element'][$value['Default']];
            }
        }

        if (array_key_exists('Text', $config)) {
            foreach ($config['Text'] as $key => $value) {
                $result[$key] = urldecode($value['Default']);
            }
        }

        return $result;
    }

    protected static function fetchView(string $path)
    {
        try {
            return View::fetch($path);
        } catch (\Throwable $e) {
            return redirect('/index/404');
        }
    }

    protected static function setViewConfig(string $themeDir = '')
    {
        if (empty($themeDir)) {
            $config = [
                'view_path' => 'view/',
                'tpl_replace_string' => ThinkConfig::get('view.tpl_replace_string')
            ];
        } else {
            $config = [
                'view_path' => 'theme/' . $themeDir . '/',
                'tpl_replace_string' => ThinkConfig::get('view.tpl_replace_string')
            ];
            $config['tpl_replace_string']['{__ThemeUrlPath__}'] = '/theme/' . $themeDir;
        }

        return View::config($config);
    }
}
