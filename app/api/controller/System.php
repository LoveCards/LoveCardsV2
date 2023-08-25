<?php

namespace app\api\controller;

//TP
use think\facade\Request;
use think\facade\Db;
use think\facade\Config;

//公共
use app\Common\Common;
use app\api\common\Common as ApiCommon;

class System
{
    //基本信息-POST
    public function site()
    {
        //验证身份并返回数据
        $userData = ApiCommon::validateAuth();
        if (!empty($userData[0])) {
            return Common::create([], $userData[1], $userData[0]);
        }
        //权限验证
        if ($userData['power'] != 0) {
            return Common::create(['power' => 1], '权限不足', 401);
        }

        $siteUrl = Request::param('siteUrl');
        if (empty($siteUrl)) {
            return Common::create([], '站点域名不得为空', 400);
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
        return Common::create([], '更新成功', 200);
    }

    //邮箱配置-POST
    public function email()
    {
        //验证身份并返回数据
        $userData = ApiCommon::validateAuth();
        if (!empty($userData[0])) {
            return Common::create([], $userData[1], $userData[0]);
        }
        //权限验证
        if ($userData['power'] != 0) {
            return Common::create(['power' => 1], '权限不足', 401);
        }

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
        return Common::create([], '更新成功', 200);
    }

    //主题设置-POST
    public function template()
    {
        //验证身份并返回数据
        $userData = ApiCommon::validateAuth();
        if (!empty($userData[0])) {
            return Common::create([], $userData[1], $userData[0]);
        }
        //权限验证
        if ($userData['power'] != 0) {
            return Common::create(['power' => 1], '权限不足', 401);
        }

        $template_directory = Request::param('templateDirectory');
        $result = ApiCommon::extraconfig('lovecards', ['template_directory' => $template_directory]);

        if ($result == true) {
            return Common::create([], '修改成功', 200);
        } else {
            return Common::create([], '修改失败，请重试', 400);
        }
    }

    //主题配置-POST
    public function TemplateSet()
    {
        // //验证身份并返回数据
        // $userData = ApiCommon::validateAuth();
        // if (!empty($userData[0])) {
        //     return Common::create([], $userData[1], $userData[0]);
        // }
        // //权限验证
        // if ($userData['power'] != 0) {
        //     return Common::create(['power' => 1], '权限不足', 401);
        // }

        $templateDirectory = Config::get('lovecards.template_directory', 'index') ?: 'index';

        $select = json_decode(Request::param('select'));

        $TemplateConfigPHP = Common::GetTemplateConfigPHP($templateDirectory, true);

        //校验元素是否合法
        foreach($select as $key => $value) {
            if(count($TemplateConfigPHP['Select'][$key]['Element']) < $value) {
                return Common::create([], '修改失败，存在非法元素', 400);
            }
        }

        //dd($select);

        $result = ApiCommon::extraThemeConfig('/index/' . $templateDirectory . '/config', $select, true, 'ThemeConfig');

        if ($result == true) {
            return Common::create([], '修改成功', 200);
        } else {
            return Common::create([], '修改失败，请重试', 400);
        }
    }

    //极验验证码配置-POST
    public function geetest()
    {
        //验证身份并返回数据
        $userData = ApiCommon::validateAuth();
        if (!empty($userData[0])) {
            return Common::create([], $userData[1], $userData[0]);
        }
        //权限验证
        if ($userData['power'] != 0) {
            return Common::create(['power' => 1], '权限不足', 401);
        }

        try {
            $data = [
                'DefSetGeetestId' => Request::param('DefSetGeetestId'),
                'DefSetGeetestKey' => Request::param('DefSetGeetestKey'),
            ];
            ApiCommon::extraconfig('lovecards', $data);
            ApiCommon::extraconfig('lovecards', ['DefSetValidatesStatus' => Request::param('DefSetValidatesStatus')], true);
            return Common::create([], '修改成功', 200);
        } catch (\Throwable $th) {
            return Common::create([], '修改失败，请重试', 400);
        }
    }
}
