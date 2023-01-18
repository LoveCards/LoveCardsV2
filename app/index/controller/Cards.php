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
    public function index()
    {
        //参数
        $ip = Common::getIp();

        //取Cards列表数据
        $listNum = 12; //每页个数
        $result = Db::table('cards')->where('state', 0)->order('id', 'desc')
            ->paginate($listNum, true);
        $cardsListRaw = $result->render();
        $listData = $result->items();

        //取标签数据
        $result = Db::table('cards_tag')->where('state', 0)->select()->toArray();
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
            'cardsListRaw'  => $cardsListRaw,
            'cardsListData'  => $listData,
            'cardsListNum'  => $listNum
        ]);

        //基础变量
        View::assign([
            'systemVer' => Common::systemVer(),
            'systemData' => Common::systemData(),
            'viewTitle'  => '卡片墙'
        ]);

        //输出模板
        return View::fetch('/cards');
    }

    //卡片详情
    public function card()
    {
        //参数
        $ip = Common::getIp();
        $id = Request::param('id');

        //验证ID取Cards数据
        $result = Db::table('cards')->where('id', $id)->findOrEmpty();
        if (!$result) {
            return Common::jumpUrl('/index/Cards/list', '卡片ID不存在');
        }
        $cardData = $result;

        //取img数据
        $result = Db::table('img')->where('aid', 1)->where('pid', $cardData['id'])->select()->toArray();
        $imgData = $result;

        //取Tag数据
        $result = Db::table('cards_tag')->where('state', 0)->select()->toArray();
        $cardsTagData = $result;
        View::assign('cardsTagData', json_encode($cardsTagData));

        //获取评论列表
        $listNum = 6; //每页个数
        $result = Db::table('cards_comments')->where('cid', $id)->where('state', 0)
            ->paginate($listNum, true);
        $cardsCommentsListRaw = $result->render();
        $listData = $result->items();

        //取Good状态合并到$cardData数据
        if (Db::table('good')->where('aid', 1)->where('ip', $ip)->where('pid', $id)->findOrEmpty() == []) {
            //未点赞
            $cardData['ipGood'] = false;
        } else {
            //已点赞
            $cardData['ipGood'] = true;
        }

        //评论列表变量
        View::assign([
            'cardsCommentsListRaw'  => $cardsCommentsListRaw,
            'cardsCommentsListData'  => $listData,
            'cardsCommentsListNum'  => $listNum
        ]);

        //卡片变量
        View::assign([
            'cardData' => $cardData,
            'imgData' => $imgData
        ]);

        //基础变量
        View::assign([
            'systemVer' => Common::systemVer(),
            'systemData' => Common::systemData(),
            'viewTitle'  => '卡片'
        ]);

        //输出模板
        return View::fetch('/card');
    }

    //添加卡片
    public function add()
    {
        //取Tag数据
        $result = Db::table('cards_tag')->where('state', 0)->select()->toArray();
        $cardsTagData = $result;
        View::assign('cardsTagData', $cardsTagData);

        //基础变量
        View::assign([
            'systemVer' => Common::systemVer(),
            'systemData' => Common::systemData(),
            'viewTitle'  => '写卡'
        ]);

        //输出模板
        return View::fetch('/cards-add');
    }

    //卡片搜索
    public function search()
    {

        //参数
        $ip = Common::getIp();
        $search = Request::param('search');

        if ($search == true) {
            //参数
            $model = Request::param('model');
            $value = Request::param('value');

            //验证Value
            if (!$value) {
                return Common::jumpUrl('/index/Cards/search', '请输入要搜索内容');
            }

            //取Cards列表
            $listNum = 12; //每页个数
            $result = Db::table('cards')->where('state', 0)->where('model', $model)->where('id|content|woName|taName|time', 'like', '%' . $value . '%')->order('id', 'desc')
                ->paginate($listNum, true);
            $cardsListRaw = $result->render();
            $listData = $result->items();

            //组合Good状态到$listData列表
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
        } else {
            //定义为空
            $cardsListRaw = [];
            $listData = [];
            $listNum = [];
        }

        //取Tag数据
        $result = Db::table('cards_tag')->where('state', 0)->select()->toArray();
        $cardsTagData = $result;
        View::assign('cardsTagData', json_encode($cardsTagData));

        //Cards分页变量
        View::assign([
            'cardsListRaw'  => $cardsListRaw,
            'cardsListData'  => $listData,
            'cardsListNum'  => $listNum
        ]);

        //基础变量
        View::assign([
            'systemVer' => Common::systemVer(),
            'systemData' => Common::systemData(),
            'viewTitle'  => '搜索'
        ]);

        //输出模板
        return View::fetch('/cards-search');
    }
}
