<?php

namespace app\api\controller\admin;

use think\facade\Request;
use think\facade\Db;
use think\facade\Config;
use think\facade\Session;

use app\common\File;
use app\common\Export;
use app\common\Theme;
use app\common\Common;
use app\common\ConfigFacade;

use app\api\controller\BaseController;

class System extends BaseController
{

    public function themes()
    {
        //当前主题
        $tDef_NowThemeDirectory = Theme::mArrayGetThemeDirectory()['N'];
        $lDef_NowThemeInfo = json_decode(File::read_file('./theme/' . $tDef_NowThemeDirectory . '/info.ini'), true);
        $lDef_NowThemeInfo['Config'] = Theme::mResultGetThemeConfig($tDef_NowThemeDirectory); //用来给前端判断主题是否可以配置
        $lDef_NowThemeInfo['DirectoryName'] = $tDef_NowThemeDirectory;
        $key = $lDef_NowThemeInfo['Name'] . $lDef_NowThemeInfo['Version'] . $lDef_NowThemeInfo['DirectoryName'];
        $lDef_NowThemeInfo['Hash'] = hash('crc32b', $key);
        if (!$lDef_NowThemeInfo) {
            $lDef_NowThemeInfo = json_decode(File::read_file('./theme/index/info.ini'), true);
        }

        //获取所有主题
        $lDef_ThemeDirectoryList = File::get_dirs('./theme')['dir'];
        sort($lDef_ThemeDirectoryList);
        $lDef_ThemeConfigList = array();
        for ($i = 2; $i < count($lDef_ThemeDirectoryList); $i++) {
            $tDef_ThemeBasePath = './theme/' . $lDef_ThemeDirectoryList[$i];
            if (File::get_size($tDef_ThemeBasePath) != 0) {
                // 以目录名为键
                // $lDef_ThemeConfigList[$lDef_ThemeDirectoryList[$i]] = json_decode(File::read_file($tDef_ThemeBasePath . '/info.ini'), true);
                // $lDef_ThemeConfigList[$lDef_ThemeDirectoryList[$i]]['DirectoryName'] = $lDef_ThemeDirectoryList[$i];
                // 无键
                $lDef_ThemeConfigList[$i - 2] = json_decode(File::read_file($tDef_ThemeBasePath . '/info.ini'), true);
                $lDef_ThemeConfigList[$i - 2]['DirectoryName'] = $lDef_ThemeDirectoryList[$i];
                //生成一个id
                $key = $lDef_ThemeConfigList[$i - 2]['Name'] . $lDef_ThemeConfigList[$i - 2]['Version'] . $lDef_ThemeConfigList[$i - 2]['DirectoryName'];
                $hash = hash('crc32b', $key);
                $lDef_ThemeConfigList[$i - 2]['Hash'] = $hash;
                $lDef_ThemeConfigList[$i - 2]['Cover'] = Request::scheme() . '://' . Request::host() . '/theme/' . $lDef_ThemeDirectoryList[$i] . '/show.png';
                //状态
                if ($lDef_NowThemeInfo['Config']) {
                    $lDef_ThemeConfigList[$i - 2]['Config'] = true;
                } else {
                    $lDef_ThemeConfigList[$i - 2]['Config'] = false;
                }
                if ($hash == $lDef_NowThemeInfo['Hash']) {
                    $lDef_ThemeConfigList[$i - 2]['Status'] = true; //如果当前主题是这个主题，则状态为true
                } else {
                    $lDef_ThemeConfigList[$i - 2]['Status'] = false; //否则状态为false
                }
            }
        }

        //config
        $tDef_NowThemeConfig = Theme::mResultGetThemeConfig(Theme::mArrayGetThemeDirectory()['N'], true);
        if ($tDef_NowThemeConfig) {
            //解码输出
            if (!empty($tDef_NowThemeConfig['Text'])) {
                foreach ($tDef_NowThemeConfig['Text'] as $key => $value) {
                    $tDef_NowThemeConfig['Text'][$key]['Default'] = urldecode($value['Default']);
                }
            }
        }

        $result = [
            "theme_list" => $lDef_ThemeConfigList,
            "theme_config" => $tDef_NowThemeConfig
        ];
        return Export::Create($result, 200);
    }

    //读取配置
    public function config()
    {
        $lDef_Result['system'] = array_column(Db::table('system')->select()->toArray(), 'value', 'name');
        $lDef_Result['master'] = $this->SYSTEM_CONFIG;
        $lDef_Result['mail'] = Config::get('mail');
        //$lDef_Result['lovecards'] = config::get('lovecards');
        return Export::Create($lDef_Result, 200);
    }

    public function setConfig()
    {
        //AI
        $params = Request::param();

        /* ---------- 1. 递归求差 ---------- */
        function array_diff_recursive(array $a, array $b): array
        {
            $result = [];
            foreach ($a as $k => $v) {
                if (!array_key_exists($k, $b)) {
                    $result[$k] = $v;
                } elseif (is_array($v) && is_array($b[$k])) {
                    $diff = array_diff_recursive($v, $b[$k]);
                    if ($diff) {
                        $result[$k] = $diff;
                    }
                } elseif ($v !== $b[$k]) {
                    $result[$k] = $v;
                }
            }
            return $result;
        }

        $params = array_diff_recursive($params, $this->SYSTEM_CONFIG);

        /* ---------- 2. 统一映射表 ---------- */
        $map = [
            // 结构:  '一级.二级' => [ 'free' => true/false ]
            'System.VisitorMode'    => ['free' => true],
            // 'System.ThemeDirectory' => ['free' => false],

            'Upload.UserImageSize'  => ['free' => true],
            'Upload.UserImageExt'   => ['free' => false],

            'UserAuth.Captcha'      => ['free' => true],

            'Cards.Approve'         => ['free' => true],
            'Cards.PictureLimit'    => ['free' => true],
            'Cards.TagLimit'        => ['free' => true],

            'Comments.Approve'      => ['free' => true],
            'Comments.PictureLimit' => ['free' => true],

            'Geetest.Status'        => ['free' => true],
            'Geetest.Id'            => ['free' => false],
            'Geetest.Key'           => ['free' => false],
        ];

        /* ---------- 3. 统一循环生成结果 ---------- */
        $lReq_Params = [];

        foreach ($map as $dotKey => $meta) {
            [$top, $sub] = explode('.', $dotKey, 2);

            if (isset($params[$top][$sub])) {
                $value = $params[$top][$sub];
                $value = gettype($value) == 'boolean' ? ($value ? 'true' : 'false')  : $value;
                $lReq_Params[$top . $sub] = [
                    'value' => $value,
                    'free'  => $meta['free'],
                ];
            }
        }

        //空值直接停止
        if (empty($lReq_Params)) {
            return Export::Create(null, 200);
        }
        //dd($lReq_Params);

        //更新数据
        $tDef_Result = ConfigFacade::mBoolSetMasterConfig($lReq_Params);

        if ($tDef_Result) {
            return Export::Create(null, 200);
        }
        return Export::Create(null, 500, '设置失败');
    }

    //基本信息-POST
    public function Site()
    {
        $siteUrl = Request::param('siteUrl');
        if (empty($siteUrl)) {
            return Export::Create(null, 400, '站点域名不得为空');
        }
        $siteName = Request::param('siteName');
        $siteICPId = Request::param('siteICPId');
        $siteKeywords = Request::param('siteKeywords');
        $siteDes = Request::param('siteDes');
        $siteTitle = Request::param('siteTitle');
        $siteCopyright = Request::param('siteCopyright');

        //更新数据
        Db::table('system')->where('name', 'siteUrl')->update(['value' => $siteUrl]);
        Db::table('system')->where('name', 'siteName')->update(['value' => $siteName]);
        Db::table('system')->where('name', 'siteICPId')->update(['value' => $siteICPId]);
        Db::table('system')->where('name', 'siteKeywords')->update(['value' => $siteKeywords]);
        Db::table('system')->where('name', 'siteDes')->update(['value' => $siteDes]);
        Db::table('system')->where('name', 'siteTitle')->update(['value' => $siteTitle]);
        Db::table('system')->where('name', 'siteCopyright')->update(['value' => $siteCopyright]);

        //返回数据
        return Export::Create(null, 200);
    }

    //邮箱配置-PATCH
    public function Email()
    {
        $lReq_Params = [
            'driver' => ['value' => Request::param('driver'), 'free' => false],
            'host' => ['value' => Request::param('host'), 'free' => false],
            'port' => ['value' => Request::param('port'), 'free' => true],
            'addr' => ['value' => Request::param('addr'), 'free' => false],
            'pass' => ['value' => Request::param('pass'), 'free' => false],
            'name' => ['value' => Request::param('name'), 'free' => false],
            'security' => ['value' => Request::param('security'), 'free' => false],
        ];

        //$lReq_Params = Common::mArrayEasyRemoveEmptyValues($lReq_Params);
        //dd($lReq_Params);
        //更新数据
        $tDef_Result = ConfigFacade::mBoolCoverConfig('mail', $lReq_Params, 'auto');

        if ($tDef_Result) {
            return Export::Create(null, 200);
        }
        return Export::Create(null, 500, '设置失败');
    }

    //主题设置-POST
    public function themeSet()
    {
        $tReq_ThemeDirectoryName = Request::param('dir');
        $tReq_ThemeInfo = json_decode(File::read_file('./theme/' . $tReq_ThemeDirectoryName . '/info.ini'), true);
        if (!$tReq_ThemeInfo) {
            return Export::Create(null, 400, '修改失败，主题信息不存在');
        }
        $tDef_LCVersionInfo = Common::mArrayGetLCVersionInfo();

        if (!($tDef_LCVersionInfo['VerS'] >= $tReq_ThemeInfo['SysVersionL'] && $tDef_LCVersionInfo['VerS'] < $tReq_ThemeInfo['SysVersionR'])) {
            return Export::Create(null, 400, '修改失败，该主题不适用当前版本');
        }

        $lReq_Params = [
            'SystemThemeDirectory' => ['value' => $tReq_ThemeDirectoryName, 'free' => false],
        ];
        $tDef_Result = ConfigFacade::mBoolSetMasterConfig($lReq_Params);

        if ($tDef_Result == true) {
            return Export::Create(null, 200);
        } else {
            return Export::Create(null, 400, '修改失败，请重试');
        }
    }

    //主题配置-POST
    public function themeConfig()
    {
        $tDef_ThemeDirectory = Config::get('master.System.ThemeDirectory', 'index') ?: 'index';

        $lReq_ParamSelect = json_decode(Request::param('select'));
        $lReq_ParamText = json_decode(Request::param('text'));

        $tDef_ThemeConfig = Theme::mResultGetThemeConfig($tDef_ThemeDirectory, true);

        $lDef_ParamThemeConfig = [];
        //校验元素是否合法
        if (!empty($lReq_ParamSelect)) {
            foreach ($lReq_ParamSelect as $key => $value) {
                if (count($tDef_ThemeConfig['Select'][$key]['Element']) < $value) {
                    return Export::Create('修改失败，Select存在非法元素', 400);
                }
                $lDef_ParamThemeConfig['Select' . $key] = $value;
            }
        }

        //转义
        if (!empty($lReq_ParamText)) {
            foreach ($lReq_ParamText as $key => $value) {
                if (empty($tDef_ThemeConfig['Text'][$key]['Name'])) {
                    return Export::Create('修改失败，Text存在非法元素', 400);
                }
                $lDef_ParamThemeConfig['Text' . $key] = $value;
            }
        }

        //更新
        $tDef_Result = Theme::mBoolCoverThemeConfig($tDef_ThemeDirectory, $lDef_ParamThemeConfig);

        if ($tDef_Result) {
            return Export::Create(null, 200);
        } else {
            return Export::Create(null, 400, '修改失败，请重试');
        }
    }

    public function updata()
    {
        /**
         * 获取公告列表（原生网络请求版）
         * 1. 先读 Session，存在直接返回
         * 2. 不存在则用 file_get_contents 请求接口
         * 3. 成功后写 Session（1 小时过期）
         *
         * @return array 公告列表（data 字段）
         */
        function getUpdata()
        {
            // 1. 从 Session 取
            $notice = Session::get('Updata');
            if ($notice !== null) {
                return $notice;
            }

            // 2. 原生 GET 请求
            $url  = 'https://proxy.gitwarp.com/https://raw.githubusercontent.com/zhiguai/LoveCards/main/VerLog.md';
            $ctx  = stream_context_create([
                'http' => [
                    'method'  => 'GET',
                    'timeout' => 5,          // 5 秒超时
                    'header'  => "User-Agent: PHP\r\n",
                ],
                'ssl'  => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $jsonStr = @file_get_contents($url, false, $ctx);
            if ($jsonStr === false) {
                // 请求失败，返回空数组
                return [];
            }

            // 4. 写入 Session 并返回
            Session::set('Updata', $jsonStr, 3600 * 3);
            return $jsonStr;
        }
        function getLatestVer()
        {
            // 1. 从 Session 取
            $notice = Session::get('LatestVer');
            if ($notice !== null) {
                return $notice;
            }

            // 2. 原生 GET 请求
            $url  = 'https://api.github.com/repositories/582292948/releases/latest';
            $ctx  = stream_context_create([
                'http' => [
                    'method'  => 'GET',
                    'timeout' => 5,          // 5 秒超时
                    'header'  => "User-Agent: PHP\r\n",
                ],
                'ssl'  => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $jsonStr = @file_get_contents($url, false, $ctx);
            if ($jsonStr === false) {
                // 请求失败，返回空数组
                return [];
            }

            // 3. 解析 JSON
            $json = json_decode($jsonStr, true);

            // 4. 写入 Session 并返回
            Session::set('LatestVer',  $json, 3600 * 3);
            return $json;
        }
        $result = [
            'ver' => Common::mArrayGetLCVersionInfo(),
            'latest' => getLatestVer(),
            'verlog' => getUpdata()
        ];
        return Export::Create($result, 200);
    }

    //其他配置-PATCH
    // public function Other()
    // {
    //     $lReq_Params = [
    //         'System' . 'VisitorMode' => ['value' => Request::param('VisitorMode'), 'free' => true],
    //         'Upload' . 'UserImageSize' => ['value' => Request::param('UserImageSize'), 'free' => true],
    //         'Upload' . 'UserImageExt' => ['value' => Request::param('UserImageExt'), 'free' => false],
    //         'UserAuth' . 'Captcha' => ['value' => Request::param('UserAuthCaptcha'), 'free' => true],
    //     ];
    //     //$lReq_Params = Common::mArrayEasyRemoveEmptyValues($lReq_Params);

    //     //更新数据
    //     $tDef_Result = ConfigFacade::mBoolSetMasterConfig($lReq_Params);

    //     if ($tDef_Result) {
    //         return Export::Create(null, 200);
    //     }
    //     return Export::Create(null, 500, '设置失败');
    // }
    //极验验证码配置-POST
    // public function Geetest()
    // {
    //     try {
    //         $data = [
    //             'DefSetGeetestId' => Request::param('DefSetGeetestId'),
    //             'DefSetGeetestKey' => Request::param('DefSetGeetestKey'),
    //         ];
    //         BackEnd::mBoolCoverConfig('lovecards', $data);
    //         BackEnd::mBoolCoverConfig('lovecards', ['DefSetValidatesStatus' => Request::param('DefSetValidatesStatus')], true);
    //         return Export::Create(null, 200);
    //     } catch (\Throwable $th) {
    //         return Export::Create(null, 400, '修改失败，请重试');
    //     }
    // }
}
