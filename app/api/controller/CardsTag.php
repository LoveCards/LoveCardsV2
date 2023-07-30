<?php

namespace app\api\controller;

//TP
use think\facade\Request;
use think\facade\Db;
use think\exception\ValidateException;

//验证
use app\api\validate\CardsTag as TagValidate;

//公共
use app\Common\Common;
use app\api\common\Common as ApiCommon;

class CardsTag extends Common
{

    protected function CAndU($id, $data, $method)
    {
        // 获取数据
        foreach ($data as $k => $v) {
            if ($v != '#') {
                $Datas[$k] = $v;
            }
        }

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
            validate(TagValidate::class)
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
            $DbResult = Db::table('cards_tag');
            $DbData = $Datas;
            // 方法选择
            if ($method == 'c') {
                $DbData['time'] = $this->NowTime;
                //默认状态:0/1
                $DbData['status'] = 0;
                //写入并返回ID
                $Id = $DbResult->insertGetId($DbData);
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
        //验证身份并返回数据
        $userData = ApiCommon::validateAuth();
        if (!empty($userData[0])) {
            return ApiCommon::create([], $userData[1], $userData[0]);
        }

        //防手抖
        $preventClicks = Common::preventClicks('LastPostTime');
        if ($preventClicks[0] == false) {
            //返回数据
            return ApiCommon::create(['prompt' => $preventClicks[1]], '添加失败', 500);
        }

        $result = self::CAndU('', [
            'tip' => Request::param('tip'),
            'name' => Request::param('name'),
        ], 'c');

        if ($result['status']) {
            return ApiCommon::create('', '添加成功', 200);
        } else {
            return ApiCommon::create($result['msg'], '添加失败', 500);
        }
    }

    //编辑-POST
    public function edit()
    {
        //验证身份并返回数据
        $userData = ApiCommon::validateAuth();
        if (!empty($userData[0])) {
            return ApiCommon::create([], $userData[1], $userData[0]);
        }

        $result = self::CAndU(Request::param('id'), [
            'tip' => Request::param('tip'),
            'name' => Request::param('name'),
            'status' => Request::param('status'),
        ], 'u');

        if ($result['status']) {
            return ApiCommon::create('', '添加成功', 200);
        } else {
            return ApiCommon::create($result['msg'], '添加失败', 500);
        }
    }

    //删除-POST
    public function delete()
    {

        //验证身份并返回数据
        $userData = ApiCommon::validateAuth();
        if (!empty($userData[0])) {
            return ApiCommon::create([], $userData[1], $userData[0]);
        }

        //获取数据
        $id = Request::param('id');

        //获取数据库对象
        $result = Db::table('cards_tag')->where('id', $id);
        if (!$result->find()) {
            return ApiCommon::create([], 'id不存在', 400);
        }
        $result->delete();

        //获取tag_map数据库对象
        $result = Db::table('cards_tag_map')->where('tid', $id);
        if ($result->find()) {
            $result->delete();
        }

        //返回数据
        return ApiCommon::create([], '删除成功', 200);
    }
}
