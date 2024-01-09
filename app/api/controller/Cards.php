<?php

namespace app\api\controller;

use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Config;

use app\api\validate\Cards as CardsValidate;
use app\api\validate\CardsSetting as CardsValidateSetting;

use app\api\model\Images as ImagesModel;

use app\common\Common;
use app\common\Export;
use app\common\BackEnd;
use app\common\App;

class Cards extends Common
{
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

    //添加-POST
    public function Add()
    {
        $context = request()->JwtData;

        $result = self::CAndU('', [
            'uid' => $context['uid'],
            'content' => Request::param('content'),

            'woName' => Request::param('woName'),
            'woContact' => Request::param('woContact'),
            'taName' => Request::param('taName'),
            'taContact' => Request::param('taContact'),

            'tag' => Request::param('tag'),
            'img' => Request::param('img'),

            'model' => Request::param('model'),
            'status' => Config::get('lovecards.api.Cards.DefSetCardsStatus')
        ], 'c');

        if ($result['status']) {
            if (Config::get('lovecards.api.Cards.DefSetCardsStatus')) {
                return Export::Create(null, 201); //添加成功,等待审核
            } else {
                return Export::Create(['id' => $result['data']], 200);
            }
        } else {
            return Export::Create($result['data'], 500, $result['msg']);
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

    //点赞-POST
    public function Good()
    {
        $context = request()->JwtData;

        //获取数据
        $id = Request::param('id');
        $ip = $this->attrGReqIp;
        $time = $this->attrGReqTime;

        //获取Cards数据库对象
        $resultCards = Db::table('cards')->where('id', $id);
        $resultCardsData = $resultCards->find();
        if (!$resultCardsData) {
            return Export::Create(null, 400, 'id不存在');
        }

        //获取good数据库对象
        $resultGood = Db::table('good');
        if ($resultGood->where('pid', $id)->where('ip', $ip)->find()) {
            return Export::Create(['请勿重复点赞'], 400, '点赞失败');
        }

        //更新视图字段
        if (!$resultCards->inc('good')->update()) {
            return Export::Create(['cards.good更新失败'], 400, '点赞失败');
        };

        $data = [
            'aid' => '1',
            'pid' => $id,
            'uid' => $context['uid'],
            'ip' => $ip,
            'time' => $time
        ];
        if (!$resultGood->insert($data)) {
            return Export::Create(['good写入失败'], 400, '点赞失败');
        };

        //返回数据
        return Export::Create([$resultCardsData['good'] + 1], 200, null);
    }
}
