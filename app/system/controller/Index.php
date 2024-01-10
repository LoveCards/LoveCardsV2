<?php

namespace app\system\controller;

use think\facade\View;

use app\common\FrontEnd;
use app\system\utils\Common;

class Index
{
    public function Install()
    {
        //检查安装状态
        $result = Common::CheckInstallLock();
        if ($result['status'] === true) {
            return FrontEnd::mObjectEasyFrontEndJumpUrl('/index/index', '检测到安装锁，如需重新安装请删除根目录[lock.txt]文件');
            exit;
        }

        //基础变量
        View::assign([
            //'systemVer' => Common::mArrayGetLCVersionInfo(),
            //'verifyEnvironment' => Install::DataVerifyEnvironment(),
            'viewTitle'  => '安装',
            'viewDescription' => false,
            'viewKeywords' => false
        ]);

        //输出模板
        return View::fetch('/install');
    }
}