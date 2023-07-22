<?php

namespace app\api\controller;

//TP类
use think\facade\Db;
use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Config;

//验证类
use app\api\validate\CardsComments as CommentsValidate;

//类
use app\Common\Common;


class CardsComments extends Common
{

    //默认评论状态ON/OFF:0/1
    const DefSetCardsCommentsState = 0;
    //默认添加评论上传图片个数
    const DefCardsSetCommentsImgNum = 3;

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
        try {
            validate(CommentsValidate::class)
                ->batch(true)
                ->check($Datas);
        } catch (ValidateException $e) {
            $validateerror = $e->getError();
            return FunResult(false, $validateerror);
        }

        // 启动事务
        Db::startTrans();
        try {
            //获取数据库对象
            $DbResult = Db::table('cards_comments');
            $DbData = $Datas;
            // 方法选择
            if ($method == 'c') {
                if (!Db::table('cards')->where('id', $Datas['cid'])->find()) {
                    return FunResult(false, 'CID不存在');
                }
                //默认状态ON/OFF:0/1
                $DbData['state'] = Config::get('lovecards.api.CardsComments.DefSetCardsCommentsState');
                //写入并返回ID
                $Id = $DbResult->insertGetId($DbData);
                //更新comments视图字段
                Db::table('cards')->where('id', $Datas['cid'])->inc('comments')->update();
            } else {
                //获取Cards数据库对象
                $DbResult = $DbResult->where('id', $id);
                if (!$DbResult->find()) {
                    return FunResult(false, 'ID不存在');
                }
                //写入并返回ID
                $DbResult->update($DbData);
            }

            // 提交事务
            Db::commit();
            return FunResult(true, '操作成功');
        } catch (\Exception $e) {
            //dd($e);
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
            'cid' => Request::param('cid'),
            'content' => Request::param('content'),
            'name' => Request::param('name'),
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
            'name' => Request::param('name'),
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

        $id = Request::param('id');
        if (!$id) {
            return Common::create(['id' => '缺少参数'], '删除失败', 400);
        }

        //获取数据库对象
        $result = Db::table('cards_comments')->where('id', $id);
        if (!$result->find()) {
            return Common::create([], 'id不存在', 400);
        }
        if (!$result->delete()) {
            return Common::create([], '删除失败', 400);
        }
        return Common::create([], '删除成功', 200);
    }
}
