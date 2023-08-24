<?php

namespace app\index\controller;

//TP类
use think\facade\View;
use think\facade\Db;

//类
use app\common\Common;

class Index
{

    //获取模板路径
    var $TemplateDirectoryPath;
    var $TemplateDirectory;
    function __construct()
    {
        //安装检测
        @$file = fopen("../lock.txt", "r");
        if (!$file) {
            header("location:/system/install");
            exit;
        }

        $this->TemplateDirectoryPath = Common::get_templateDirectory()[0];
        $this->TemplateDirectory = Common::get_templateDirectory()[1];

        //公共模板变量
        View::assign([
            'TemplateDirectory' => '/view/index/' . $this->TemplateDirectory . '/assets', //模板路径
            'TemplateConfigPHP' => Common::GetTemplateConfigPHP($this->TemplateDirectory), //模板配置
            'systemVer' => Common::systemVer(), //程序版本信息
            'systemData' => Common::systemData(), //系统配置信息
            'viewKeywords' => false,
            'viewDescription' => false,
        ]);
    }

    //输出
    public function index()
    {
        //参数
        $ip = Common::getIp();

        //取Cards列表数据
        $hotListNum = 8; //每页个数
        $topListNum = 32; //每页个数

        //取Cards.top数据
        $result = Db::table('cards')->where('status', 0)->where('top', 1)->order('id', 'desc')
            ->limit($topListNum)->select()->toArray();
        $listData = $result;
        //取Cards推荐数据
        //$result = Db::table('cards')->where('status', 0)->where('top', 0)->order(['good','comment'=>'desc'])
        //->limit($hotListNum)->select()->toArray();
        $result = Db::query("select * from cards where top = '0' and status = '0' order by IF(ISNULL(woName),1,0),comments*0.3+good*0.7 desc limit 0," . $hotListNum);
        //合并到$listData数据
        $listData = array_merge($listData, $result);

        //dd($listData);

        //取标签数据
        $result = Db::table('cards_tag')->where('status', 0)->select()->toArray();
        $cardsTagData = $result;
        View::assign('cardsTagData', json_encode($cardsTagData));

        //取Good状态合并到$listData数据
        for ($i = 0; $i < sizeof($listData); $i++) {
            $resultGood = Db::table('good')->where('aid', 1)->where('ip', $ip);
            //查找对应封面
            if ($resultGood->where('pid', $listData[$i]['id'])->findOrEmpty() == []) {
                //未点赞
                $listData[$i]['ipGood'] = false;
            } else {
                //已点赞
                $listData[$i]['ipGood'] = true;
            }
        }

        //Cards分页变量;
        View::assign([
            'cardsListData'  => $listData,
        ]);

        //基础变量
        View::assign([
            'viewTitle'  => '推荐',
        ]);

        //输出模板
        return View::fetch($this->TemplateDirectoryPath . '/index');
    }

    public function error()
    {
        $code = request()->param('code');

        //输出模板
        if ($code == 404) {
            View::assign([
                'viewTitle'  => '页面走丢了',
                'viewKeywords' => '',
                'viewDescription' => ''
            ]);
            return View::fetch('../admin/error/404');
        } else {
            return redirect('/');
        }
    }
}
