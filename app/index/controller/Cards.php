<?php

namespace app\index\controller;

//视图功能
use think\facade\View;
//TPDb类
use think\facade\Db;
//
use think\facade\Request;

//公共类
use app\common\Common;

class Cards
{

    //列表
    public function list()
    {
        //获取LC配置
        View::assign('systemVer', Common::systemVer());
        //获取系统配置
        View::assign('systemData', Common::systemData());
        // 批量赋值
        View::assign([
            'viewTitle'  => '卡片墙'
        ]);

        //获取卡片列表
        $listNum = 6; //每页个数
        $result = Db::table('cards')
            ->paginate($listNum, true);

        //组合数据
        $cardsListRaw = $result->render();
        $listData = $result->items();
        // for ($i = 0; $i < sizeof($listData); $i++) {
        //     //查找对应封面
        //     $result = Db::table('img')
        //         ->where('pid', $listData[$i]['id'])
        //         ->findOrEmpty();
        //     if (empty($result)) {
        //         $listData[$i]['imgUrl'] = false;
        //     }else{
        //         $listData[$i]['imgUrl'] = $result['url'];
        //     }
        // }

        //dd($listData);
        View::assign([
            'cardsListRaw'  => $cardsListRaw,
            'cardsListData'  => $listData,
            'cardsListNum'  => $listNum
        ]);

        //输出模板
        return View::fetch('/cards-list');
    }

    //
    public function card()
    {
        //获取Card数据
        $result = Db::table('cards')->where('id', Request::param('id'))->findOrEmpty();
        if (!$result) {
            return Common::jumpUrl('/index/Cards/list', '卡片ID不存在');
        }
        $cardData = $result;
        //获取image数据
        $result = Db::table('img')->where('aid', 1)->where('pid', $cardData['id'])->select()->toArray();
        $imgData = $result;

        //获取卡片数据
        View::assign('cardData', $cardData);
        //获取关联图片数据
        View::assign('imgData', $imgData);
        //获取卡片数据
        //View::assign($cardData);
        //获取LC配置
        View::assign('systemVer', Common::systemVer());
        //获取系统配置
        View::assign('systemData', Common::systemData());
        // 批量赋值
        View::assign([
            'viewTitle'  => '表白卡'
        ]);

        //输出模板
        return View::fetch('/cards');
    }

    public function add()
    {
        //获取用户信息
        //View::assign($userData[1]);
        //获取LC配置
        View::assign('systemVer', Common::systemVer());
        //获取系统配置
        View::assign('systemData', Common::systemData());
        // 批量赋值
        View::assign([
            'viewTitle'  => '写卡'
        ]);
        //输出模板
        return View::fetch('/cards-add');
    }

    public function search()
    {
        //获取用户信息
        //View::assign($userData[1]);
        //获取LC配置
        View::assign('systemVer', Common::systemVer());
        //获取系统配置
        View::assign('systemData', Common::systemData());
        // 批量赋值
        View::assign([
            'viewTitle'  => '搜索'
        ]);
        //输出模板
        return View::fetch('/cards-search');
    }
}
