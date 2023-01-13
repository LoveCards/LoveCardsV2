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

    //卡片列表
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

        $ip = Common::getIp();

        //获取卡片列表
        $listNum = 6; //每页个数
        $result = Db::table('cards')
            ->paginate($listNum, true);

        //组合数据
        $cardsListRaw = $result->render();
        $listData = $result->items();

        //组合点赞状态到列表
        //dd($resultGoodData->where('pid',65)->findOrEmpty() == []);
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

        //获取标签数据
        $result = Db::table('cards_tag')->where('state', 0)->select()->toArray();
        $cardsTagData = $result;
        View::assign('cardsTagData', json_encode($cardsTagData));

        //dd($listData);
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

    //卡片详情
    public function card()
    {
        $ip = Common::getIp();
        $id = Request::param('id');

        //获取Card数据
        $result = Db::table('cards')->where('id', $id)->findOrEmpty();
        if (!$result) {
            return Common::jumpUrl('/index/Cards/list', '卡片ID不存在');
        }
        $cardData = $result;
        //获取image数据
        $result = Db::table('img')->where('aid', 1)->where('pid', $cardData['id'])->select()->toArray();
        $imgData = $result;

        //获取点赞状态合并数据
        if (Db::table('good')->where('aid', 1)->where('ip', $ip)->where('pid', $id)->findOrEmpty() == []) {
            //未点赞
            $cardData['ipGood'] = false;
        } else {
            //已点赞
            $cardData['ipGood'] = true;
        }

        //获取卡片数据
        View::assign('cardData', $cardData);
        //获取关联图片数据
        View::assign('imgData', $imgData);

        //获取评论列表
        $listNum = 6; //每页个数
        $result = Db::table('cards_comments')->where('cid',$id)->where('state',0)
            ->paginate($listNum, true);

        //组合数据
        $cardsCommentsListRaw = $result->render();
        $listData = $result->items();

        View::assign([
            'cardsCommentsListRaw'  => $cardsCommentsListRaw,
            'cardsCommentsListData'  => $listData,
            'cardsCommentsListNum'  => $listNum
        ]);

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

    //添加卡片
    public function add()
    {
        //获取用户信息
        //View::assign($userData[1]);
        //获取LC配置
        View::assign('systemVer', Common::systemVer());
        //获取系统配置
        View::assign('systemData', Common::systemData());
        //获取标签数据
        $result = Db::table('cards_tag')->where('state', 0)->select()->toArray();
        $cardsTagData = $result;
        View::assign('cardsTagData', $cardsTagData);
        // 批量赋值
        View::assign([
            'viewTitle'  => '写卡'
        ]);
        //输出模板
        return View::fetch('/cards-add');
    }

    //卡片搜索
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
        return View::fetch('/cards-search-env');
    }
}
