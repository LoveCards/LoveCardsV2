<?php

namespace app\index\method;

use think\facade\View;
use think\facade\Db;
use think\facade\Request;
use think\facade\Config;

use app\api\model\Images as ImagesModel;
use app\common\BackEnd;
use app\common\FrontEnd;
use app\common\Common;
use app\index\BaseController;

trait Cards
{
    //通用卡片查询*
    public function CommonCardList()
    {
        $BaseController = new BaseController;
        //整理参数
        $tReq_ParamModel = Request::param('model');
        $tReq_ParamModel == 0 ? $tReq_ParamModel = 0 : $tReq_ParamModel = 1;

        $tDef_CardListMax = 12; //每页个数
        $tDef_ListName = 'CommonCardList';

        //查询数据
        $lDef_Result = Db::table('cards')->alias('CARD')
            ->field($BaseController::G_Def_DbCardsCommonField)
            ->leftJoin('good GOOD', $BaseController::G_Def_DbCardsCommonJoin . "'$BaseController->attrGReqIp'")
            ->where('CARD.status', 0)
            ->where('CARD.deleted_at', null)
            ->where('CARD.data', 'like', '%"model": "' . $tReq_ParamModel . '"%')
            ->order('CARD.id', 'desc')
            ->paginate($tDef_CardListMax, true);
        $tDef_CardsEasyPagingComponent = $lDef_Result->render();
        $lDef_CardList = $lDef_Result->items();

        //兼容旧的
        $lDef_CardList = $this->dataToArray($lDef_CardList);
        $lDef_CardList = $this->dataCompatible($lDef_CardList);

        //分配变量
        return Common::mArrayEasyReturnStruct(null, true, [
            $tDef_ListName => array_merge(
                $BaseController->mArrayEasyGetAssignCardList($tDef_ListName, $lDef_CardList, $tDef_CardsEasyPagingComponent, $tDef_CardListMax),
                [
                    'CardsModel' => $tReq_ParamModel,
                    'ViewTitle'  => '卡片墙',
                ]
            )
        ]);
    }

    // 推荐列表*
    public function HotCardList()
    {
        $BaseController = new BaseController;
        //整理参数
        define("CONST_G_TOP_LISTS_MAX", 32); //置顶卡片列表最大个数
        define("CONST_G_HOT_LISTS_MAX", 8); //热门卡片列表最大个数
        $tDef_ListName = 'HotCardList';

        //查询数据
        $lDef_Result = Db::table('cards')
            ->where('status', 0)
            ->where('is_top', 1)
            ->where('deleted_at', null)
            ->order('id', 'desc')
            ->limit(CONST_G_TOP_LISTS_MAX)
            ->select()->toArray();
        $lDef_CardLists = $lDef_Result;
        //Cards推荐列表
        //$result = Db::table('cards')->where('status', 0)->where('top', 0)->order(['good','comment'=>'desc'])
        //->limit(CONST_G_HOT_LISTS_MAX)->select()->toArray();
        $lDef_Result = Db::query("select * from cards where is_top = 0 and status = 0 and deleted_at IS NULL order by comments*0.3+good*0.7 desc limit 0," . CONST_G_HOT_LISTS_MAX);
        //合并推荐列表到置顶列表
        $lDef_CardLists = array_merge($lDef_CardLists, $lDef_Result);
        //取Good状态合并到CardList数据
        for ($i = 0; $i < sizeof($lDef_CardLists); $i++) {
            $lDef_Result = Db::table('good')->where('aid', 1)->where('ip', $BaseController->attrGReqIp);
            //查找对应封面
            if ($lDef_Result->where('pid', $lDef_CardLists[$i]['id'])->findOrEmpty() == []) {
                //未点赞
                $lDef_CardLists[$i]['ipGood'] = false;
            } else {
                //已点赞
                $lDef_CardLists[$i]['ipGood'] = true;
            }
        }

        //兼容旧的
        $lDef_CardLists = $this->dataToArray($lDef_CardLists);
        $lDef_CardLists = $this->dataCompatible($lDef_CardLists);

        //分配变量
        return Common::mArrayEasyReturnStruct(null, true, [
            $tDef_ListName => array_merge(
                $BaseController->mArrayEasyGetAssignCardList($tDef_ListName, $lDef_CardLists),
                ['ViewTitle' => '热门']
            )
        ]);
    }

    // 搜索列表*
    public function SearchCardList()
    {
        $BaseController = new BaseController;
        // 整理参数
        $tReq_ParamSearchStatus = Request::param('search');
        $tReq_ParamModel = Request::param('model');
        $tReq_ParamSearchValue = Request::param('value');

        $tDef_ViewTitle = '搜索';

        $tDef_CardListMax = 12; // 每页个数
        $tDef_ListName = 'SearchCardList';

        // 查询数据
        if ($tReq_ParamSearchStatus) {
            // 参数
            $tDef_ViewTitle = $tReq_ParamSearchValue . '的搜索结果';

            // 验证Value
            if (!$tReq_ParamSearchValue) {
                return FrontEnd::mObjectEasyFrontEndJumpUrl('./search', '请输入要搜索内容');
            }

            if ($tReq_ParamModel != 'false') {
                $tReq_ParamModel = $tReq_ParamModel == 1 ? 1 : 0;
                $tDef_Result = Db::table('cards')->where('status', 0)->where('deleted_at', null)->where('data', 'like', '%"model": "' . $tReq_ParamModel . '"%');
            } else {
                $tDef_Result = Db::table('cards')->where('status', 0)->where('deleted_at', null);
            }

            // 取 Cards 列表
            $tDef_Result = $tDef_Result->where('id|content|data', 'like', '%' . $tReq_ParamSearchValue . '%')->order('id', 'desc')
                ->paginate($tDef_CardListMax, true);
            $tDef_CardsEasyPagingComponent = $tDef_Result->render();
            $lDef_CardList = $tDef_Result->items();

            // 组合 Good 状态到 $tListData 列表
            foreach ($lDef_CardList as &$card) {
                $tResultGood = Db::table('good')->where('aid', $BaseController->attrGReqAppId['cards'])->where('ip', $BaseController->attrGReqIp);
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
            $tDef_CardsEasyPagingComponent = [];
            $lDef_CardList = [];
            $tDef_CardListMax = [];
        }

        //兼容旧的
        $lDef_CardList = $this->dataToArray($lDef_CardList);
        $lDef_CardList = $this->dataCompatible($lDef_CardList);

        // 分配变量
        return Common::mArrayEasyReturnStruct(null, true, [
            $tDef_ListName => array_merge(
                $BaseController->mArrayEasyGetAssignCardList($tDef_ListName, $lDef_CardList, $tDef_CardsEasyPagingComponent, $tDef_CardListMax),
                [
                    'ViewTitle'  => $tDef_ViewTitle,
                ]
            )
        ]);
    }

    // TAG合集列表*
    public function TagCardList()
    {
        $BaseController = new BaseController;
        // 整理参数
        $tReq_TagIdValue = Request::param('value');

        $tDef_CardListMax = 12; // 每页个数

        $tDef_ViewTitle = 'Tag合集';
        $tDef_ListName = 'TagCardList';

        // 查询数据
        if (!$tReq_TagIdValue) {
            // 定义为空
            $tDef_CardsEasyPagingComponent = [];
            $lDef_CardList = [];
            $tDef_CardListMax = [];
        } else {
            $tReq_TagId = Db::table('tags')->where('id', $tReq_TagIdValue)->where('aid', $BaseController->attrGReqAppId['cards'])->where('status', 0)->findOrEmpty();
            if ($tReq_TagId) {
                $lDef_Result = Db::table('tags_map')->alias('CTM')
                    ->join('cards CARD', 'CTM.pid = CARD.id')
                    ->field($BaseController::G_Def_DbCardsCommonField)
                    ->leftJoin('good GOOD', $BaseController::G_Def_DbCardsCommonJoin . "'$BaseController->attrGReqIp'")
                    ->where('CTM.tag_id', $tReq_TagIdValue)
                    ->order('CARD.id', 'desc')
                    ->paginate($tDef_CardListMax, true);
                $tDef_CardsEasyPagingComponent = $lDef_Result->render();
                $lDef_CardList = $lDef_Result->items();

                $tDef_ViewTitle = '关于' . $tReq_TagId['name'] . '的卡片合集';
            } else {
                // 定义为空
                $tDef_CardsEasyPagingComponent = [];
                $lDef_CardList = [];
                $tDef_CardListMax = [];
            }
        }

        //兼容旧的
        $lDef_CardList = $this->dataToArray($lDef_CardList);
        $lDef_CardList = $this->dataCompatible($lDef_CardList);

        // 分配变量
        return Common::mArrayEasyReturnStruct(null, true, [
            $tDef_ListName => array_merge(
                $BaseController->mArrayEasyGetAssignCardList($tDef_ListName, $lDef_CardList, $tDef_CardsEasyPagingComponent, $tDef_CardListMax),
                [
                    'ViewTitle' => $tDef_ViewTitle,
                    'ViewTagName' => $tReq_TagId['name']
                ]
            )
        ]);
    }

    // TAG列表
    public function TagList()
    {
        $BaseController = new BaseController;
        $tReq_ParamModel = Request::param('model');
        // 查询数据
        View::assign($BaseController->mArrayEasyGetAssignCardTagList());
        return Common::mArrayEasyReturnStruct(null, true, [
            'TagList' => [
                'CardModel' => $tReq_ParamModel
            ]
        ]);
    }

    // 卡片详情*
    public function Card()
    {
        $BaseController = new BaseController;
        // 整理参数
        $tReq_ParamId = Request::param('id');
        //dd($tReq_ParamId);

        $tDef_CommentsMax = 6; // 每页个数
        $tDef_ListName = 'Card';

        // 查询数据
        $lDef_CardData = Db::table('cards')->alias('CARD')
            ->field($BaseController::G_Def_DbCardsCommonField)
            ->leftJoin('good GOOD', $BaseController::G_Def_DbCardsCommonJoin . "'$BaseController->attrGReqIp'")
            ->where('CARD.id', $tReq_ParamId)
            ->where('CARD.status', 0)
            ->where('CARD.deleted_at', null)
            ->findOrEmpty();
        if (empty($lDef_CardData)) {
            return redirect('/404');
        }

        $lDef_CardData = $this->dataToArray([$lDef_CardData]);
        $lDef_CardData = $this->dataCompatible($lDef_CardData)[0];

        if ($lDef_CardData) {
            // 防止快速刷新网页增加浏览量
            $tDef_PreventClicks = BackEnd::mRemindEasyDebounce('LastGetTimeCardID' . $tReq_ParamId, 60);
            if ($tDef_PreventClicks[0] == true) {
                // 获取 Cards 数据库对象
                $lDef_ResultCards = Db::table('cards')->where('id', $tReq_ParamId);
                // 更新视图字段
                if (!$lDef_ResultCards->inc('views')->update()) {
                    //return Common::create(['cards.views' => 'cards.views更新失败'], '无效浏览', 400);
                };
                $lDef_CardData['look'] = $lDef_CardData['look'] + 1;
            }
            // 获取图片数据
            $tDef_ImgData = ImagesModel::where('aid', $BaseController->attrGReqAppId['cards'])->where('pid', $lDef_CardData['id'])->select()->toArray();
            // 获取评论列表
            $lDef_Result = Db::table('comments')->where('aid', $BaseController->attrGReqAppId['cards'])->where('pid', $tReq_ParamId)->where('status', 0)->order('id', 'desc')->paginate($tDef_CommentsMax, true);

            //兼容旧版数据库写法 评论名称获取合并
            $comments_data = $lDef_Result->toArray();
            $user_ids = [];
            foreach ($comments_data['data'] as $key => $value) {
                $user_ids[] = $value['user_id'];
            };
            $lDef_Result_user = Db::table('users')->whereIn('id', $user_ids)->where('status', 0)->select();
            $userIndex = [];
            foreach ($lDef_Result_user as $item) {
                //键替换为ID
                $userIndex[$item['id']] = $item;
            }
            foreach ($comments_data['data'] as $key => $value) {
                //处理兼容
                $comments_data['data'][$key]['time'] = $comments_data['data'][$key]['created_at'];
                //合并UID
                $uid = $comments_data['data'][$key]['user_id'];
                if ($uid == 0) {
                    $comments_data['data'][$key]['name'] = '匿名';
                } else if (array_key_exists($uid, $userIndex)) {
                    $comments_data['data'][$key]['name'] = $userIndex[$uid]['username'];
                } else {
                    $comments_data['data'][$key]['name'] = '匿名';
                }
            }

            $tDef_CommentsEasyPagingComponent = $lDef_Result->render();
            $tDef_Comments = $comments_data['data'];
        } else {
            $tDef_CommentsEasyPagingComponent = [];
            $tDef_Comments = [];
            $tDef_CommentsMax = [];
            $lDef_CardData = [];
            $tDef_ImgData = [];
        }

        // 分配变量
        $lDef_AssignData['CardCommentListEasyPagingComponent'] = $tDef_CommentsEasyPagingComponent;
        $lDef_AssignData['CardCommentList'] = $tDef_Comments;
        $lDef_AssignData['CardCommentListMax'] = $tDef_CommentsMax;
        $lDef_AssignData['CardData'] =  $lDef_CardData;
        $lDef_AssignData['CardImgList'] = $tDef_ImgData;

        if ($lDef_CardData) {
            !$lDef_CardData['woName'] ? $lDef_CardData['woName'] = '匿名' : 0;
            $lDef_AssignData['ViewTitle'] =  $lDef_CardData['woName'] . '的卡片';
            $lDef_AssignData['ViewDescription'] =  $lDef_CardData['woName'] . '表白' .  $lDef_CardData['woName'] . '说' .  $lDef_CardData['content'];
            $lDef_AssignData['ViewKeywords'] =  $lDef_CardData['woName'] . ',' .  $lDef_CardData['taName'] . ',LoveCards,表白卡';
        } else {
            $lDef_AssignData['ViewTitle'] =  '卡片详情';
            $lDef_AssignData['ViewDescription'] =  '卡片详情';
            $lDef_AssignData['ViewKeywords'] =  '卡片详情,LoveCards,表白卡';
        }
        return Common::mArrayEasyReturnStruct(null, true, [
            $tDef_ListName => $lDef_AssignData
        ]);
    }

    //适配旧版本方法
    protected function dataToArray($data)
    {
        $result = [
            'woName' => null,
            'taName' => null,
            'woContact' => null,
            'taContact' => null,
            'model' => 0,
        ];

        $return = [];
        foreach ($data as $item) {
            if ($item['data']) {
                $result = array_merge($result, json_decode($item['data'], true));
            }
            unset($item['data']);
            $return[] = array_merge($item, $result);
        }
        return $return;
    }
    protected function dataCompatible($data)
    {
        foreach ($data as $key => $item) {
            $data[$key]['top'] = $data[$key]['is_top'];
            $data[$key]['uid'] = $data[$key]['user_id'];
            $data[$key]['time'] = $data[$key]['created_at'];
            $data[$key]['ip'] = $data[$key]['post_ip'];
            $data[$key]['look'] = $data[$key]['views'];
            $data[$key]['img'] = $data[$key]['cover'];
            $data[$key]['tag'] = $data[$key]['tags'];
            unset($data[$key]['is_top']);
            unset($data[$key]['user_id']);
            unset($data[$key]['created_at']);
            unset($data[$key]['post_ip']);
            unset($data[$key]['views']);
            unset($data[$key]['cover']);
            unset($data[$key]['tags']);
        }

        return $data;
    }
}
