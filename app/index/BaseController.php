<?php

namespace app\index;

use think\facade\View;
use think\facade\Db;
use think\facade\Config;

use app\common\Common;
use app\common\Theme;
use app\common\File;

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

    //获取模板路径
    var $attrGDefNowThemeDirectoryPath;
    var $attrGDefNowThemeDirectoryName;

    function __construct()
    {
        //安装检测
        @$file = fopen("../lock.txt", "r");
        if (!$file) {
            header("location:/system/install");
            exit;
        }

        //基础变量
        $this->attrGReqTime = date('Y-m-d H:i:s');
        $this->attrGReqIp = $this->mStringGetIP();
        $this->attrGDefNowThemeDirectoryPath = Theme::mArrayGetThemeDirectory()['P'];
        $this->attrGDefNowThemeDirectoryName = Theme::mArrayGetThemeDirectory()['N'];
        $this->attrGReqView = '/app/' . strtolower(request()->controller()) . '/' . request()->action();

        //主题dark模式支持
        $lRes_ThemeConfig = Theme::mResultGetThemeConfig($this->attrGDefNowThemeDirectoryName);
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
        Theme::mObjectEasySetViewConfig($this->attrGDefNowThemeDirectoryName);

        //JS文件校验引入
        File::read_file(dirname(dirname(dirname(__FILE__))) . '/public/' . $this->attrGDefNowThemeDirectoryName . $this->attrGReqView . '.js') ?
            $lDef_AssignData['ViewActionJS'] = true :
            0;

        //公共模板变量
        View::assign([
            'ThemeAssetsUrlPath' => '/theme/' . $this->attrGDefNowThemeDirectoryName . '/assets', //模板路径
            'ThemeConfig' => $lRes_ThemeConfig, //模板配置
            'LCVersionInfo' => Common::mArrayGetLCVersionInfo(), //程序版本信息
            'SystemData' => Common::mArrayGetDbSystemData(), //系统配置信息
            'SystemConfig' => config::get('lovecards'),
            'ViewKeywords' => false,
            'ViewDescription' => false,
            'ViewActionJS' => false
        ]);
    }

    protected function mObjectEasyAssignCards($lDef_CardsList, $tDef_CardsListEasyPagingComponent, $tDef_CardsListMax)
    {
        //赋值Cards相关变量;
        View::assign([
            'CardsList'  => $lDef_CardsList,
            'CardsListEasyPagingComponent'  => $tDef_CardsListEasyPagingComponent,
            'CardsListMax'  => $tDef_CardsListMax
        ]);
    }

    protected function mObjectEasyGetAndAssignCardsTags()
    {
        //获取并赋值CardsTag相关变量
        $lDef_Result = Db::table('cards_tag')->where('status', 0)->select()->toArray();
        View::assign([
            'CardsTagsListJson' => json_encode($lDef_Result),
            'CardsTagsList' => $lDef_Result
        ]);
    }
}
