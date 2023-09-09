<?php

namespace app\index\controller;

use think\facade\View;
use think\facade\Db;

use app\common\Common;
use app\common\Theme;
use app\index\BaseController;

class Index extends BaseController
{
    public function Index()
    {
        define("CONST_G_TOP_LISTS_MAX", 32); //置顶卡片列表最大个数
        define("CONST_G_HOT_LISTS_MAX", 8); //热门卡片列表最大个数

        //Cards置顶列表
        $var_l_def_result = Db::table('cards')
            ->where('status', 0)
            ->where('top', 1)
            ->order('id', 'desc')
            ->limit(CONST_G_TOP_LISTS_MAX)
            ->select()->toArray();
        $var_l_def_cards_lists = $var_l_def_result;
        //Cards推荐列表
        //$result = Db::table('cards')->where('status', 0)->where('top', 0)->order(['good','comment'=>'desc'])
        //->limit(CONST_G_HOT_LISTS_MAX)->select()->toArray();
        $var_l_def_result = Db::query("select * from cards where top = '0' and status = '0' order by IF(ISNULL(woName),1,0),comments*0.3+good*0.7 desc limit 0," . CONST_G_HOT_LISTS_MAX);
        //合并推荐列表到置顶列表
        $var_l_def_cards_lists = array_merge($var_l_def_cards_lists, $var_l_def_result);
        //取Good状态合并到CardsList数据
        for ($i = 0; $i < sizeof($var_l_def_cards_lists); $i++) {
            $var_l_def_result = Db::table('good')->where('aid', 1)->where('ip', $this->attrGReqIp);
            //查找对应封面
            if ($var_l_def_result->where('pid', $var_l_def_cards_lists[$i]['id'])->findOrEmpty() == []) {
                //未点赞
                $var_l_def_cards_lists[$i]['ipGood'] = false;
            } else {
                //已点赞
                $var_l_def_cards_lists[$i]['ipGood'] = true;
            }
        }

        //Tag列表
        $var_l_def_result = Db::table('cards_tag')->where('status', 0)->select()->toArray();
        View::assign([
            'CardsTagsListJson' => json_encode($var_l_def_result),
            'CardsTagsList' => $var_l_def_result
        ]);

        //Cards列表;
        View::assign('CardsList', $var_l_def_cards_lists);

        //基础变量
        View::assign([
            'ViewTitle'  => '推荐',
        ]);

        //输出模板
        return View::fetch('/index');
    }

    public function Error()
    {
        $code = request()->param('code');

        //恢复view为系统配置
        Theme::mObjectEasySetViewConfig('');

        //输出模板
        if ($code == 404) {
            View::assign([
                'ViewTitle'  => '页面走丢了',
                'ViewKeywords' => '',
                'ViewDescription' => ''
            ]);
            return View::fetch('../error/404');
        } else {
            return redirect('/');
        }
    }
}
