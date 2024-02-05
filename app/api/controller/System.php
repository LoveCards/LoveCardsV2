<?php

namespace app\api\controller;

use think\facade\Request;
use think\facade\Db;
use think\facade\Config;

use app\common\File;
use app\common\Export;
use app\common\BackEnd;
use app\common\Theme;
use app\common\Common;
use app\common\ConfigFacade;

class System
{

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

    //邮箱配置-Get
    public function GetEmail()
    {
        $lDef_Result = Config::get('mail');
        return Export::Create($lDef_Result, 200);
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

    //获取其他配置-Get
    public function GetOther()
    {
        $lDef_Result = ConfigFacade::mArrayGetMasterConfig();
        return Export::Create($lDef_Result, 200);
    }

    //其他配置-PATCH
    public function Other()
    {
        $lReq_Params = [
            'System' . 'VisitorMode' => ['value' => Request::param('VisitorMode'), 'free' => true],
            'Upload' . 'UserImageSize' => ['value' => Request::param('UserImageSize'), 'free' => true],
            'Upload' . 'UserImageExt' => ['value' => Request::param('UserImageExt'), 'free' => false],
            'UserAuth' . 'Captcha' => ['value' => Request::param('UserAuthCaptcha'), 'free' => true],
        ];
        //$lReq_Params = Common::mArrayEasyRemoveEmptyValues($lReq_Params);

        //更新数据
        $tDef_Result = ConfigFacade::mBoolSetMasterConfig($lReq_Params);

        if ($tDef_Result) {
            return Export::Create(null, 200);
        }
        return Export::Create(null, 500, '设置失败');
    }

    //主题设置-POST
    public function Template()
    {
        $tReq_ThemeDirectoryName = Request::param('themeDirectory');
        $tReq_ThemeInfo = json_decode(File::read_file('./theme/' . $tReq_ThemeDirectoryName . '/info.ini'), true);
        $tDef_LCVersionInfo = Common::mArrayGetLCVersionInfo();

        if (!($tDef_LCVersionInfo['VerS'] >= $tReq_ThemeInfo['SysVersionL'] && $tDef_LCVersionInfo['VerS'] < $tReq_ThemeInfo['SysVersionR'])) {
            return Export::Create(null, 400, '修改失败，该主题不适用当前版本');
        }

        $tDef_Result = BackEnd::mBoolCoverConfig('lovecards', ['theme_directory' => $tReq_ThemeDirectoryName]);

        if ($tDef_Result == true) {
            return Export::Create(null, 200);
        } else {
            return Export::Create(null, 400, '修改失败，请重试');
        }
    }

    //主题配置-POST
    public function TemplateSet()
    {
        $tDef_ThemeDirectory = Config::get('lovecards.theme_directory', 'index') ?: 'index';

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

    //极验验证码配置-POST
    public function Geetest()
    {
        try {
            $data = [
                'DefSetGeetestId' => Request::param('DefSetGeetestId'),
                'DefSetGeetestKey' => Request::param('DefSetGeetestKey'),
            ];
            BackEnd::mBoolCoverConfig('lovecards', $data);
            BackEnd::mBoolCoverConfig('lovecards', ['DefSetValidatesStatus' => Request::param('DefSetValidatesStatus')], true);
            return Export::Create(null, 200);
        } catch (\Throwable $th) {
            return Export::Create(null, 400, '修改失败，请重试');
        }
    }
}
