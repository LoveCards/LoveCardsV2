<?php

namespace app\api\controller;

use think\facade\Db;
use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Config;

use app\api\validate\Comments as CommentsValidate;
use app\common\App;
use app\common\Common;
use app\common\Export;

class Comments extends Common
{
    protected function CAndU($id, $data, $method)
    {
        // 获取数据
        foreach ($data as $k => $v) {
            if ($v != '#') {
                $Datas[$k] = $v;
            }
        }
        // 数据校验
        try {
            if ($method == 'u') {
                validate(CommentsValidate::class)
                    ->remove('aid', 'require')
                    ->batch(true)
                    ->check($Datas);
            } else {
                validate(CommentsValidate::class)
                    ->batch(true)
                    ->check($Datas);
            }
        } catch (ValidateException $e) {
            $validateerror = $e->getError();
            return Common::mArrayEasyReturnStruct('格式错误', false, $validateerror);
        }

        // 启动事务
        Db::startTrans();
        try {
            //获取数据库对象
            $DbResult = Db::table('comments');
            $DbData = $Datas;
            // 方法选择
            if ($method == 'c') {
                $DbData['time'] = $this->attrGReqTime;
                $DbData['ip'] = $this->attrGReqIp;
                //校验AID与PID是否存在
                $tRes_CheckAidAndPid = App::mArrayCheckAidAndPid($DbData['aid'], $DbData['pid']);
                if (!$tRes_CheckAidAndPid['status']) {
                    return Common::mArrayEasyReturnStruct($tRes_CheckAidAndPid['msg'], false);
                }
                //默认状态ON/OFF:0/1
                $DbData['status'] = Config::get('lovecards.api.CardsComments.DefSetCardsCommentsStatus');
                //写入并返回ID
                $Id = $DbResult->insertGetId($DbData);
                //更新comments视图字段
                Db::table('cards')->where('id', $DbData['pid'])->inc('comments')->update();
            } else {
                //获取Cards数据库对象
                $DbResult = $DbResult->where('id', $id);
                if (!$DbResult->find()) {
                    return Common::mArrayEasyReturnStruct('ID不存在', false);
                }
                //写入并返回ID
                $DbResult->update($DbData);
            }

            // 提交事务
            Db::commit();
            return Common::mArrayEasyReturnStruct('操作成功');
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
            'aid' => Request::param('aid'),
            'pid' => Request::param('pid'),
            'uid' => $context['uid'],
            'content' => Request::param('content'),
            'name' => Request::param('name')
        ], 'c');

        if ($result['status']) {
            if (Config::get('lovecards.api.CardsComments.DefSetCardsCommentsStatus')) {
                return Export::Create(null, 201);
            } else {
                return Export::Create(null, 200);
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
            'name' => Request::param('name'),
            'status' => Request::param('status')
        ], 'u');

        if ($result['status']) {
            return Export::Create(null, 200);
        } else {
            return Export::Create($result['data'], 500, $result['msg']);
        }
    }

    //删除-POST
    public function Delete()
    {
        $id = Request::param('id');
        if (!$id) {
            return Export::Create(null, 400, 'id缺失');
        }

        //获取数据库对象
        $result = Db::table('comments')->where('id', $id);
        if (!$result->find()) {
            return Export::Create(null, 500, 'id不存在');
        }
        if (!$result->delete()) {
            return Export::Create(null, 500, '删除失败');
        }
        return Export::Create(null, 200);
    }
}
