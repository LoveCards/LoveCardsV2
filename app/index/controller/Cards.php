<?php

namespace app\index\controller;

//TP类
use think\facade\View;
use think\facade\Db;
use think\facade\Request;
use think\facade\Config;

//类
use app\common\BackEnd;
use app\common\FrontEnd;
use app\index\BaseController;

class Cards extends BaseController
{

    //Index
    public function Index()
    {
        $tReq_ParamModel = Request::param('model');
        if ($tReq_ParamModel == 0) {
            $tReq_ParamModel = 0;
        } else {
            $tReq_ParamModel = 1;
        }
        View::assign('CardsModel', $tReq_ParamModel);

        //取Cards列表数据
        $tDef_CardsListsMax = 12; //每页个数
        $lDef_Result = Db::table('cards')->alias('CARD')
            ->field(self::G_Def_DbCardsCommonField)
            ->leftJoin('good GOOD', self::G_Def_DbCardsCommonJoin . "'$this->attrGReqIp'")
            ->where('CARD.status', 0)
            ->where('CARD.model', $tReq_ParamModel)
            ->order('CARD.id', 'desc')
            ->paginate($tDef_CardsListsMax, true);
        $tDef_CardsEasyPagingComponent = $lDef_Result->render();
        $lDef_CardsLists = $lDef_Result->items();
        $this->mObjectEasyAssignCards($lDef_CardsLists, $tDef_CardsEasyPagingComponent, $tDef_CardsListsMax); //Cards相关变量

        $this->mObjectEasyGetAndAssignCardsTags(); //获取并赋值CardsTag相关变量

        //基础变量
        View::assign([
            'ViewTitle'  => '卡片墙',
        ]);

        //输出模板
        return View::fetch($this->attrGDefNowThemeDirectoryPath . '/cards');
    }

    // 卡片详情
    public function Card()
    {
        $tReq_ParamId = Request::param('id');

        // 验证ID并获取 Cards 数据

        $lDef_CardData = Db::table('cards')->alias('CARD')
            ->field(self::G_Def_DbCardsCommonField)
            ->leftJoin('good GOOD', self::G_Def_DbCardsCommonJoin . "'$this->attrGReqIp'")
            ->where('CARD.id', $tReq_ParamId)
            ->where('CARD.status', 0)
            ->findOrEmpty();
        if (!$lDef_CardData) {
            return FrontEnd::mObjectEasyFrontEndJumpUrl('/index/Cards', '卡片ID不存在');
        }

        // 防止快速刷新网页增加浏览量
        $tDef_PreventClicks = BackEnd::mRemindEasyDebounce('LastGetTimeCardID' . $tReq_ParamId, 60);
        if ($tDef_PreventClicks[0] == true) {
            // 获取 Cards 数据库对象
            $lDef_ResultCards = Db::table('cards')->where('id', $tReq_ParamId);
            // 更新视图字段
            if (!$lDef_ResultCards->inc('look')->update()) {
                //return Common::create(['cards.look' => 'cards.look更新失败'], '无效浏览', 400);
            };
            $lDef_CardData['look'] = $lDef_CardData['look'] + 1;
        }

        // 获取图片数据
        $tDef_ImgData = Db::table('img')->where('aid', 1)->where('pid', $lDef_CardData['id'])->select()->toArray();

        // 获取 Tag 数据
        $this->mObjectEasyGetAndAssignCardsTags();

        // 获取评论列表
        $tDef_CommentsListsMax = 6; // 每页个数
        $lDef_Result = Db::table('cards_comments')->where('cid', $tReq_ParamId)->where('status', 0)->order('id', 'desc')
            ->paginate($tDef_CommentsListsMax, true);
        $tDef_CommentsListsEasyPagingComponent = $lDef_Result->render();
        $tDef_CommentsLists = $lDef_Result->items();

        // 评论列表变量
        View::assign([
            'CardCommentsListsEasyPagingComponent' => $tDef_CommentsListsEasyPagingComponent,
            'CardCommentsLists' => $tDef_CommentsLists,
            'CardCommentsListsMax' => $tDef_CommentsListsMax
        ]);

        // 卡片变量
        View::assign([
            'CardData' =>  $lDef_CardData,
            'CardImgLists' => $tDef_ImgData
        ]);

        if (!$lDef_CardData['woName']) {
            $lDef_CardData['woName'] = '匿名';
        }
        // 基础变量
        View::assign([
            'ViewTitle' =>  $lDef_CardData['woName'] . '的卡片',
            'ViewDescription' =>  $lDef_CardData['woName'] . '表白' .  $lDef_CardData['woName'] . '说' .  $lDef_CardData['content'],
            'ViewKeywords' =>  $lDef_CardData['woName'] . ',' .  $lDef_CardData['taName'] . ',LoveCards,表白卡'
        ]);

        // 输出模板
        return View::fetch($this->attrGDefNowThemeDirectoryPath . '/card');
    }

    // 添加卡片
    public function Add()
    {
        $tReq_ParamModel = Request::param('model');
        $tReq_ParamModel = $tReq_ParamModel == 0 ? 0 : 1;
        View::assign('CardModel', $tReq_ParamModel);

        // 取 Tag 数据
        $lDef_Result = Db::table('cards_tag')->where('status', 0)->select()->toArray();
        $tDef_CardsTagData = $lDef_Result;
        View::assign([
            'CardsTagsListsJson' => json_encode($tDef_CardsTagData),
            'CardsTagsLists' => $tDef_CardsTagData
        ]);

        // 基础变量
        View::assign([
            'ViewTitle' => '写卡',
        ]);

        // 输出模板
        return View::fetch($this->attrGDefNowThemeDirectoryPath . '/cards-add');
    }


    // 卡片搜索
    public function Search()
    {
        // 参数
        $tReq_ParamSearchStatus = Request::param('search');
        $tDef_ViewTitle = '搜索';

        if ($tReq_ParamSearchStatus) {
            // 参数
            $tReq_ParamModel = Request::param('model');
            $tReq_ParamSearchValue = Request::param('value');
            $tDef_ViewTitle = $tReq_ParamSearchValue . '的搜索结果';

            // 验证Value
            if (!$tReq_ParamSearchValue) {
                return FrontEnd::mObjectEasyFrontEndJumpUrl('/index/Cards/search', '请输入要搜索内容');
            }

            if ($tReq_ParamModel != 'false') {
                $tReq_ParamModel = $tReq_ParamModel == 1 ? 1 : 0;
                $tDef_Result = Db::table('cards')->where('status', 0)->where('model', $tReq_ParamModel);
            } else {
                $tDef_Result = Db::table('cards')->where('status', 0);
            }

            // 取 Cards 列表
            $tDef_CardsListsMax = 12; // 每页个数
            $tDef_Result = $tDef_Result->where('id|content|woName|taName', 'like', '%' . $tReq_ParamSearchValue . '%')->order('id', 'desc')
                ->paginate($tDef_CardsListsMax, true);
            $tDef_CardsListsEasyPagingComponent = $tDef_Result->render();
            $lDef_CardsLists = $tDef_Result->items();

            // 组合 Good 状态到 $tListData 列表
            foreach ($lDef_CardsLists as &$card) {
                $tResultGood = Db::table('good')->where('aid', 1)->where('ip', $this->attrGReqIp);
                // 查找对应封面
                if ($tResultGood->where('pid', $card['id'])->findOrEmpty() == []) {
                    // 未点赞
                    $card['ipGood'] = false;
                } else {
                    // 已点赞
                    $card['ipGood'] = true;
                }
            }
        } else {
            // 定义为空
            $tDef_CardsListsEasyPagingComponent = [];
            $lDef_CardsLists = [];
            $tDef_CardsListsMax = [];
        }

        // 取 Tag 数据
        $lDef_Result = Db::table('cards_tag')->where('status', 0)->select()->toArray();
        View::assign([
            'CardsTagsListsJson' => json_encode($lDef_Result),
            'CardsTagsLists' => $lDef_Result
        ]);

        //Cards分页变量;
        View::assign([
            'CardsListsEasyPagingComponent'  => $tDef_CardsListsEasyPagingComponent,
            'CardsLists'  => $lDef_CardsLists,
            'CardsListsMax'  => $tDef_CardsListsMax
        ]);
        // 基础变量
        View::assign([
            'ViewTitle' => $tDef_ViewTitle,
        ]);

        // 输出模板
        return View::fetch($this->attrGDefNowThemeDirectoryPath . '/cards-search');
    }

    // TAG集合
    public function Tag()
    {
        // 传入Tid
        $tReq_TagIdValue = Request::param('value');

        // 验证Value
        if (!$tReq_TagIdValue) {
            return FrontEnd::mObjectEasyFrontEndJumpUrl('/index/Cards/search', '请输入Tag');
        }
        $tReq_TagId = Db::table('cards_tag')->where('id', $tReq_TagIdValue)->findOrEmpty();
        if (!$tReq_TagId) {
            return FrontEnd::mObjectEasyFrontEndJumpUrl('/index/Cards/search', 'Tag已被删除或不存在');
        }

        $tDef_ViewTitle = '关于' . $tReq_TagId['name'] . '的卡片合集';

        // 取 cards_tag_map 列表
        $tDef_CardsListsMax = 12; // 每页个数
        $lDef_Result = Db::table('cards_tag_map')->alias('CTM')
            ->join('cards CARD', 'CTM.cid = CARD.id')
            ->field(self::G_Def_DbCardsCommonField)
            ->leftJoin('good GOOD', self::G_Def_DbCardsCommonJoin . "'$this->attrGReqIp'")
            ->where('CTM.tid', $tReq_TagIdValue)
            ->order('CARD.id', 'desc')
            ->paginate($tDef_CardsListsMax, true);
        $tDef_CardsListsEasyPagingComponent = $lDef_Result->render();
        $lDef_CardsLists = $lDef_Result->items();
        $this->mObjectEasyAssignCards($lDef_CardsLists, $tDef_CardsListsEasyPagingComponent, $tDef_CardsListsMax); //赋值Cards相关变量

        $this->mObjectEasyGetAndAssignCardsTags(); //获取并赋值CardsTag相关变量

        // 基础变量
        View::assign([
            'ViewTitle' => $tDef_ViewTitle,
            'ViewTagName' => $tReq_TagId['name']
        ]);

        // 输出模板
        return View::fetch($this->attrGDefNowThemeDirectoryPath . '/cards-tag');
    }
}
