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

class System
{

    //中间件
    protected $middleware = [\app\api\middleware\AdminPowerCheck::class];

    //基本信息-POST
    public function Site()
    {
        $siteUrl = Request::param('siteUrl');
        if (empty($siteUrl)) {
            return Export::mObjectEasyCreate([], '站点域名不得为空', 400);
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
        return Export::mObjectEasyCreate([], '更新成功', 200);
    }

    //邮箱配置-POST
    public function Email()
    {
        $LCEAPI = Request::param('LCEAPI');
        $smtpUser = Request::param('smtpUser');
        $smtpHost = Request::param('smtpHost');
        $smtpPort = Request::param('smtpPort');
        $smtpPass = Request::param('smtpPass');
        $smtpName = Request::param('smtpName');
        $smtpSecure = Request::param('smtpSecure');

        //更新数据
        Db::table('system')->where('name', 'LCEAPI')->update(['value' => $LCEAPI]);
        Db::table('system')->where('name', 'smtpUser')->update(['value' => $smtpUser]);
        Db::table('system')->where('name', 'smtpHost')->update(['value' => $smtpHost]);
        Db::table('system')->where('name', 'smtpPort')->update(['value' => $smtpPort]);
        Db::table('system')->where('name', 'smtpPass')->update(['value' => $smtpPass]);
        Db::table('system')->where('name', 'smtpName')->update(['value' => $smtpName]);
        Db::table('system')->where('name', 'smtpSecure')->update(['value' => $smtpSecure]);

        //返回数据
        return Export::mObjectEasyCreate([], '更新成功', 200);
    }

    //主题设置-POST
    public function Template()
    {
        $tReq_ThemeDirectoryName = Request::param('themeDirectory');
        $tReq_ThemeInfo = json_decode(File::read_file('./theme/' . $tReq_ThemeDirectoryName . '/info.ini'), true);
        $tDef_LCVersionInfo = Common::mArrayGetLCVersionInfo();

        if (!($tDef_LCVersionInfo['VerS'] >= $tReq_ThemeInfo['SysVersionL'] && $tDef_LCVersionInfo['VerS'] < $tReq_ThemeInfo['SysVersionR'])) {
            return Export::mObjectEasyCreate([], '修改失败，该主题不适用当前版本', 400);
        }

        $tDef_Result = BackEnd::mBoolCoverConfig('lovecards', ['theme_directory' => $tReq_ThemeDirectoryName]);

        if ($tDef_Result == true) {
            return Export::mObjectEasyCreate([], '修改成功', 200);
        } else {
            return Export::mObjectEasyCreate([], '修改失败，请重试', 400);
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
                    return Export::mObjectEasyCreate([], '修改失败，Select存在非法元素', 400);
                }
                $lDef_ParamThemeConfig['Select' . $key] = $value;
            }
        }

        //转义
        if (!empty($lReq_ParamText)) {
            foreach ($lReq_ParamText as $key => $value) {
                if (empty($tDef_ThemeConfig['Text'][$key]['Name'])) {
                    return Export::mObjectEasyCreate([], '修改失败，Text存在非法元素', 400);
                }
                $lDef_ParamThemeConfig['Text' . $key] = $value;
            }
        }

        //更新
        $tDef_Result = Theme::mBoolCoverThemeConfig($tDef_ThemeDirectory, $lDef_ParamThemeConfig);

        if ($tDef_Result) {
            return Export::mObjectEasyCreate([], '修改成功', 200);
        } else {
            return Export::mObjectEasyCreate([], '修改失败，请重试', 400);
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
            return Export::mObjectEasyCreate([], '修改成功', 200);
        } catch (\Throwable $th) {
            return Export::mObjectEasyCreate([], '修改失败，请重试', 400);
        }
    }
}
