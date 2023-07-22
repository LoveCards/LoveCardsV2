<?php

namespace app\api\controller;

//TP类
use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Config;

//验证类
use app\api\validate\Cards as CardsValidate;

//公共类
use app\Common\Common;

class Cards extends Common
{
    //操作函数
    protected function CAndU($id, $data, $method)
    {
        // 获取数据
        $Datas = $data;
        $Datas['time'] = $this->NowTime;
        $Datas['ip'] = $this->ReqIp;

        // 返回结果
        function FunResult($status, $msg, $id = '')
        {
            return [
                'status' => $status,
                'msg' => $msg,
                'id' => $id
            ];
        }

        // 数据校验
        switch ((int)$Datas['model']) {
                //
            case 1:
                try {
                    validate(CardsValidate::class)
                        ->batch(true)
                        ->check($Datas);
                } catch (ValidateException $e) {
                    $validateerror = $e->getError();
                    return FunResult(false, $validateerror);
                }
                break;
                //默认
            default:
                try {
                    validate(CardsValidate::class)
                        ->batch(true)
                        ->remove('taName', 'require')
                        ->check($Datas);
                } catch (ValidateException $e) {
                    $validateerror = $e->getError();
                    return FunResult(false, $validateerror);
                }
                break;
        }

        // 启动事务
        Db::startTrans();
        try {
            //获取数据库对象
            $DbResult = Db::table('cards');
            $DbData = $Datas;
            $DbData['img'] = '';
            $DbData['tag'] = '';
            // 方法选择
            if ($method == 'c') {
                //默认卡片状态ON/OFF:0/1
                $DbData['state'] = Config::get('lovecards.api.Cards.DefSetCardsState');
                $CardId = $DbResult->insertGetId($DbData); //写入并返回ID
            } else {
                //获取Cards数据库对象
                $DbResult = Db::table('cards')->where('id', $id);
                if (!$DbResult->find()) {
                    return FunResult(false, 'ID不存在');
                }
                //写入并返回ID
                $DbResult->update($DbData);
                $CardId = $id;
                //清理原始数据
                Db::table('img')->where('pid', $id)->delete();
                Db::table('cards_tag_map')->where('cid', $id)->delete();
            }

            //写入img
            $img = json_decode($Datas['img'], true);
            if (!empty($img)) {
                $JsonData = array();
                foreach ($img as $key => $value) {
                    $JsonData[$key]['aid'] = 1;
                    $JsonData[$key]['pid'] = $CardId;
                    $JsonData[$key]['url'] = $value;
                    $JsonData[$key]['time'] = $this->NowTime;
                }
                Db::table('img')->insertAll($JsonData);
                //更新img视图字段
                $DbResult->where('id', $CardId)->update(['img' => $img[0]]);
            }

            //写入tag
            $tag = json_decode($Datas['tag'], true);
            if (!empty($tag)) {
                //构建数据数组
                $JsonData = array();
                foreach ($tag as $key => $value) {
                    $JsonData[$key]['cid'] = $CardId;
                    $JsonData[$key]['tid'] = $value;
                    $JsonData[$key]['time'] = $this->NowTime;
                }
                Db::table('cards_tag_map')->insertAll($JsonData);
                //更新tag视图字段
                $DbResult->where('id', $CardId)->update(['tag' => Json_encode($tag)]);
            }

            // 提交事务
            Db::commit();
            return FunResult(true, '操作成功', $CardId);
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return FunResult(false, '操作失败');
        }
    }

    //添加-POST
    public function add()
    {
        //防手抖
        $preventClicks = Common::preventClicks('LastPostTime');
        if ($preventClicks[0] == false) {
            //返回数据
            return Common::create(['prompt' => $preventClicks[1]], '添加失败', 500);
        }

        $result = self::CAndU('', [
            'content' => Request::param('content'),

            'woName' => Request::param('woName'),
            'woContact' => Request::param('woContact'),
            'taName' => Request::param('taName'),
            'taContact' => Request::param('taContact'),

            'tag' => Request::param('tag'),
            'img' => Request::param('img'),

            'model' => Request::param('model'),
            'state' => '1'
        ], 'c');

        if ($result['status']) {
            return Common::create(['id' => $result['id']], '添加成功', 200);
        } else {
            return Common::create($result['msg'], '添加失败', 500);
        }
    }

    //编辑-POST
    public function edit()
    {

        //验证身份并返回数据
        $userData = Common::validateAuth();
        if (!empty($userData[0])) {
            return Common::create([], $userData[1], $userData[0]);
        }

        $result = self::CAndU(Request::param('id'), [
            'content' => Request::param('content'),

            'woName' => Request::param('woName'),
            'woContact' => Request::param('content'),
            'taName' => Request::param('taName'),
            'taContact' => Request::param('taContact'),

            'tag' => Request::param('tag'),
            'img' => Request::param('img'),

            'top' => Request::param('top'),
            'model' => Request::param('model'),
            'state' => Request::param('state')
        ], 'u');

        if ($result['status']) {
            return Common::create(['id' => $result['id']], '添加成功', 200);
        } else {
            return Common::create($result['msg'], '添加失败', 500);
        }
    }

    //删除-POST
    public function delete()
    {

        //验证身份并返回数据
        $userData = Common::validateAuth();
        if (!empty($userData[0])) {
            return Common::create([], $userData[1], $userData[0]);
        }

        //获取数据
        $id = Request::param('id');

        //获取Cards数据库对象
        $result = Db::table('cards')->where('id', $id);
        if (!$result->find()) {
            return Common::create([], 'id不存在', 400);
        }
        $result->delete();

        //获取img数据库对象
        $result = Db::table('img')->where('pid', $id);
        if ($result->find()) {
            $result->delete();
        }

        //获取tag数据库对象
        $result = Db::table('cards_tag_map')->where('cid', $id);
        if ($result->find()) {
            $result->delete();
        }

        //获取comments数据库对象
        $result = Db::table('cards_comments')->where('cid', $id);
        if ($result->find()) {
            $result->delete();
        }

        //返回数据
        return Common::create([], '删除成功', 200);
    }

    //点赞-POST
    public function good()
    {
        //获取数据
        $id = Request::param('id');
        $ip = Common::getIp();
        $time = date('Y-m-d H:i:s');

        //获取Cards数据库对象
        $resultCards = Db::table('cards')->where('id', $id);
        $resultCardsData = $resultCards->find();
        if (!$resultCardsData) {
            return Common::create([], 'id不存在', 400);
        }

        //获取good数据库对象
        $resultGood = Db::table('good');
        if ($resultGood->where('pid', $id)->where('ip', $ip)->find()) {
            return Common::create(['tip' => '请勿重复点赞'], '点赞失败', 400);
        }

        //更新视图字段
        if (!$resultCards->inc('good')->update()) {
            return Common::create(['cards.good' => 'cards.good更新失败'], '点赞失败', 400);
        };

        $data = ['aid' => '1', 'pid' => $id, 'ip' => $ip, 'time' => $time];
        if (!$resultGood->insert($data)) {
            return Common::create(['good' => 'good写入失败'], '点赞失败', 400);
        };

        //返回数据
        return Common::create(['Num' => $resultCardsData['good'] + 1], '点赞成功', 200);
    }
}
