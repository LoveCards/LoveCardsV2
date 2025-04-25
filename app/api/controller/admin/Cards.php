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

class Cards extends Common
{

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

    //操作函数
    protected function CAndU($id, $data, $method)
    {
        $lDef_AppCardsID = APP::mArrayGetAppTableMapValue('cards')['data'];

        // 获取数据
        foreach ($data as $k => $v) {
            if ($v != '#') {
                $Datas[$k] = $v;
            }
        }

        // 数据校验
        try {
            switch ((int)$Datas['model']) {
                case 1:
                    validate(CardsValidate::class)
                        ->batch(true)
                        ->remove('taName', 'require')
                        ->check($Datas);
                    break;
                default:
                    validate(CardsValidate::class)
                        ->batch(true)
                        ->check($Datas);
                    break;
            }
        } catch (ValidateException $e) {
            $validateerror = $e->getError();
            return Common::mArrayEasyReturnStruct('格式错误', false, $validateerror);
        }


        // 启动事务
        Db::startTrans();
        try {
            //获取数据库对象
            $DbResult = Db::table('cards');
            $DbData = $Datas;
            $DbData['time'] = $this->attrGReqTime;
            $DbData['ip'] = $this->attrGReqIp;
            $DbData['img'] = '';
            $DbData['tag'] = '';
            // 方法选择
            if ($method == 'c') {
                //默认卡片状态ON/OFF:0/1
                $DbData['status'] = Config::get('lovecards.api.Cards.DefSetCardsStatus');
                $CardId = $DbResult->insertGetId($DbData); //写入并返回ID
            } else {
                //获取Cards数据库对象
                $DbResult = Db::table('cards')->where('id', $id);
                if (!$DbResult->find()) {
                    return Common::mArrayEasyReturnStruct('ID不存在', false);
                }
                //写入并返回ID
                $DbResult->update($DbData);
                $CardId = $id;
                //清理原始数据
                //ImagesModel::destroy(ImagesModel::where('aid', $lDef_AppCardsID)->where('pid', $id)->column('id'));
                Db::table('images')->where('aid', $lDef_AppCardsID)->where('pid', $id)->delete();
                Db::table('tags_map')->where('aid', $lDef_AppCardsID)->where('pid', $id)->delete();
            }

            //写入img
            $img = json_decode($Datas['img'], true);
            if (!empty($img)) {
                $JsonData = array();
                foreach ($img as $key => $value) {
                    $JsonData[$key]['uid'] = $Datas['uid'];
                    $JsonData[$key]['aid'] = $lDef_AppCardsID;
                    $JsonData[$key]['pid'] = $CardId;
                    $JsonData[$key]['url'] = $value;
                }
                $ImagesModel = new ImagesModel;
                $ImagesModel->saveAll($JsonData);
                //更新img视图字段
                $DbResult->where('id', $CardId)->update(['img' => $img[0]]);
            }

            //写入tag
            $tag = json_decode($Datas['tag'], true);
            if (!empty($tag)) {
                //构建数据数组
                $JsonData = array();
                foreach ($tag as $key => $value) {
                    $JsonData[$key]['aid'] = $lDef_AppCardsID;
                    $JsonData[$key]['pid'] = $CardId;
                    $JsonData[$key]['tid'] = $value;
                }
                Db::table('tags_map')->insertAll($JsonData);
                //更新tag视图字段
                $DbResult->where('id', $CardId)->update(['tag' => Json_encode($tag)]);
            }

            // 提交事务
            Db::commit();
            return Common::mArrayEasyReturnStruct('操作成功', true,  $CardId);
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return Common::mArrayEasyReturnStruct('操作失败', false, $e->getMessage());
        }
    }

    //编辑-POST
    public function Edit()
    {
        $result = self::CAndU(Request::param('id'), [
            'uid' => Request::param('uid'),
            'content' => Request::param('content'),

            'woName' => Request::param('woName'),
            'woContact' => Request::param('woContact'),
            'taName' => Request::param('taName'),
            'taContact' => Request::param('taContact'),

            'tag' => Request::param('tag'),
            'img' => Request::param('img'),

            'top' => Request::param('top'),
            'model' => Request::param('model'),
            'status' => Request::param('status')
        ], 'u');

        if ($result['status']) {
            return Export::Create(['id' => $result['data']], 200);
        } else {
            return Export::Create($result['data'], 500, '编辑失败');
        }
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
