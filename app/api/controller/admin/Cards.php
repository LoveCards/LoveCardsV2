<?php

namespace app\api\controller\admin;

use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Config;

use app\api\validate\Cards as CardsValidate;
use app\api\validate\CardsSetting as CardsValidateSetting;

use app\api\model\Images as ImagesModel;

use app\api\service\Cards as CardsService;

use app\common\Common;
use app\common\Export;
use app\common\BackEnd;
use app\common\App;

use yunarch\app\api\controller\Utils as ApiControllerUtils;
use yunarch\app\api\controller\IndexUtils as ApiControllerIndexUtils;
use yunarch\app\api\validate\Index as ApiIndexValidate;
use yunarch\app\api\validate\Get as ApiGetValidate;

class Cards extends Common
{

    public function Get()
    {
        // 获取参数
        $params = Request::param();

        //验证参数
        try {
            validate(ApiGetValidate::class)
                ->batch(true)
                ->check($params);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $error = $e->getError();
            return Export::Create($error, 400, '参数错误');
        }

        //调用服务
        $lDef_Result = CardsService::Get($params['id']);
        //返回结果
        return Export::Create($lDef_Result['data'], 200, null);
    }

    public function Index()
    {
        // 获取参数并按照规则过滤
        $params = ApiControllerUtils::filterParams(Request::param(), ApiIndexValidate::$all_scene['index']);
        // search_keys转数组
        $params = ApiControllerIndexUtils::paramsJsonToArray('search_keys', $params);

        //验证参数
        try {
            validate(ApiIndexValidate::class)
                ->batch(true)
                ->check($params);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $error = $e->getError();
            return Export::Create($error, 400, '参数错误');
        }
        //调用服务
        $lDef_Result = CardsService::Index($params);
        //返回结果
        return Export::Create($lDef_Result['data'], 200, null);
    }

    //编辑-Patch
    public function Patch()
    {
        // 获取参数并按照规则过滤
        $params = ApiControllerUtils::filterParams(Request::param(), CardsValidate::$all_scene['admin']['patch']);

        //验证参数
        try {
            validate(CardsValidate::class)
                ->batch(true)
                ->check($params);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $error = $e->getError();
            return Export::Create($error, 400, '参数错误');
        }

        //调用服务
        $lDef_Result = CardsService::updateCard($params);

        //返回结果
        return Export::Create($lDef_Result['data'], 200, null);
    }

    //删除-POST
    public function Delete()
    {
        $lDef_AppCardsID = APP::mArrayGetAppTableMapValue('cards')['data'];
        //获取数据
        $id = Request::param('id');

        //获取Cards数据库对象
        $result = Db::table('cards')->where('id', $id);
        if (!$result->find()) {
            return Export::Create(null, 400, 'id不存在');
        }
        $result->delete();

        //获取img数据库对象
        $result = Db::table('images')->where('aid', $lDef_AppCardsID)->where('pid', $id);
        if ($result->find()) {
            $result->delete();
        }

        //获取tag数据库对象
        $result = Db::table('tags_map')->where('aid', $lDef_AppCardsID)->where('pid', $id);
        if ($result->find()) {
            $result->delete();
        }

        //获取comments数据库对象
        $result = Db::table('comments')->where('aid', $lDef_AppCardsID)->where('pid', $id);
        if ($result->find()) {
            $result->delete();
        }

        //返回数据
        return Export::Create(null, 200);
    }

    //设置-POST
    public function Setting()
    {

        $data = [
            'DefSetCardsImgNum' => Request::param('DefSetCardsImgNum'),
            'DefSetCardsTagNum' => Request::param('DefSetCardsTagNum'),
            'DefSetCardsStatus' => Request::param('DefSetCardsStatus'),
            'DefSetCardsImgSize' => Request::param('DefSetCardsImgSize'),
            'DefSetCardsCommentsStatus' => Request::param('DefSetCardsCommentsStatus')
        ];

        // 数据校验
        try {
            validate(CardsValidateSetting::class)
                ->batch(true)
                ->check($data);
        } catch (ValidateException $e) {
            $validateerror = $e->getError();
            return Export::Create($validateerror, 400, '修改失败');
        }

        $result = BackEnd::mBoolCoverConfig('lovecards', $data, true);

        if ($result == true) {
            return Export::Create(null, 200);
        } else {
            return Export::Create(null, 400, '修改失败，请重试');
        }
    }
}
