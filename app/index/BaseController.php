<?php

namespace app\index;

use think\facade\View;
use think\facade\Db;
use think\facade\Config;

use app\common\Common;
use app\common\Theme;
use app\common\File;
use app\common\App;

class BaseController extends Common
{

    const G_Def_DbCardsCommonField = [
        'CARD.id',
        'CARD.content',
        'CARD.img',
        'CARD.woName',
        'CARD.woContact',
        'CARD.taName',
        'CARD.taContact',
        'CARD.good',
        'CARD.comments',
        'CARD.look',
        'CARD.tag',
        'CARD.model',
        'CARD.time',
        'CARD.ip',
        'CARD.top',
        'CARD.status',
        'IF(GOOD.id IS NOT NULL, TRUE, FALSE) AS ipGood', // 使用 IF 函数判断是否存在匹配项
    ]; //Cards通用查询字段

    const G_Def_DbCardsCommonJoin = 'GOOD.aid = 1 AND GOOD.pid = CARD.id AND GOOD.ip = '; //Cards通用点赞查询关联

    //基础参数
    var $attrGReqTime;
    var $attrGReqIp;
    var $attrGReqView;
    var $attrGReqContext;
    var $attrGReqAppId;
    var $attrGReqAssignArray;

    function __construct()
    {
        //安装检测
        @$file = fopen("../lock.txt", "r");
        if (!$file) {
            header("location:/system/index/install");
            exit;
        }

        //基础变量
        $this->attrGReqTime = date('Y-m-d H:i:s');
        $this->attrGReqIp = $this->mStringGetIP();
        $this->attrGReqView = [
            'Theme' => [
                'DirectoryPath' => Theme::mArrayGetThemeDirectory()['P'],
                'DirectoryName' => Theme::mArrayGetThemeDirectory()['N']
            ]
        ];
        $this->attrGReqAppId = [
            'cards' => App::mArrayGetAppTableMapValue('cards')['data']
        ];

        //获取主题配置
        $lRes_ThemeConfig = Theme::mResultGetThemeConfig($this->attrGReqView['Theme']['DirectoryName']);
        if ($lRes_ThemeConfig === false) {
            $lRes_ThemeConfig = [];
        }
        //获取主题原配置
        $this->attrGReqView['Theme']['Config'] = Theme::mResultGetThemeConfig($this->attrGReqView['Theme']['DirectoryName'], true);

        //主题dark模式支持
        //dd(cookie('ThemeDark'));
        if (array_key_exists('ThemeDark', $lRes_ThemeConfig)) {
            if (cookie('ThemeDark') != null) {
                if (cookie('ThemeDark') == "false") {
                    $lRes_ThemeConfig['ThemeDark'] = false;
                } else {
                    $lRes_ThemeConfig['ThemeDark'] = true;
                }
            }
        }

        //根据主题覆盖模板配置
        Theme::mObjectEasySetViewConfig($this->attrGReqView['Theme']['DirectoryName']);

        //公共模板变量
        $this->attrGReqAssignArray = [
            'ThemeUrlPath' => '/theme/' . $this->attrGReqView['Theme']['DirectoryName'], //模板路径
            'ThemeAssetsUrlPath' => '/theme/' . $this->attrGReqView['Theme']['DirectoryName'] . '/assets', //模板路径
            'ThemeConfig' => $lRes_ThemeConfig, //模板配置

            'LCVersionInfo' => Common::mArrayGetLCVersionInfo(), //程序版本信息

            'SystemData' => Common::mArrayGetDbSystemData(), //系统配置信息
            'SystemConfig' => config::get('lovecards'),
            'SystemControllerName' => strtolower(request()->controller()), //当前控制器名称
            'SystemActionName' => request()->action(), //当前方法名

            'ViewKeywords' => false,
            'ViewDescription' => false,
            'ViewActionJS' => false
        ];
    }

    public function mArrayEasyGetAssignCardList($lDef_ListName, $lDef_CardList, $tDef_CardListEasyPagingComponent = null, $tDef_CardListMax = null)
    {
        $tDef_CardListEasyPagingComponent != null ?
            $lDef_ArrayData['CardListEasyPagingComponent'] = $tDef_CardListEasyPagingComponent :
            $lDef_ArrayData['CardListEasyPagingComponent'] = null;

        $tDef_CardListMax != null ? $lDef_ArrayData['CardListMax'] = $tDef_CardListMax :
            $lDef_ArrayData['CardListMax'] = null;

        $lDef_ArrayData['CardList'] = $lDef_CardList;

        //赋值Cards相关变量;
        return $lDef_ArrayData;
    }

    public function mArrayEasyGetAssignCardTagList()
    {
        //获取并赋值CardsTag相关变量
        $lDef_Result = Db::table('tags')->where('aid', $this->attrGReqAppId['cards'])->where('status', 0)->select()->toArray();
        return [
            'CardsTagsListJson' => json_encode($lDef_Result),
            'CardsTagsList' => $lDef_Result
        ];
    }
}
