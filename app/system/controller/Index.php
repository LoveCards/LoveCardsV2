<?php

namespace app\system\controller;

use think\facade\View;
use think\facade\Cookie;

use app\system\utils\Common;

class Index
{
    public function Install()
    {
        $result = Common::CheckInstallLock();
        if ($result === true) {
            Cookie::forever('msg', '检测到安装锁，如需重新安装请删除根目录[lock.txt]文件');
            return redirect('/index/index');
        }

        View::assign([
            'viewTitle'  => '安装',
            'viewDescription' => false,
            'viewKeywords' => false
        ]);

        return View::fetch('/install');
    }
}