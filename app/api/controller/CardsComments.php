<?php

namespace app\api\controller;

use think\facade\Db;
use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Config;

use app\api\validate\CardsComments as CommentsValidate;

use app\common\Common;
use app\Common\Export;

class CardsComments extends Common
{

    //中间件
    protected $middleware = [
        \app\api\middleware\AdminAuthCheck::class => [
            'only' => [
                'Edit',
                'Delet'
            ]
        ],
        \app\api\middleware\SessionDebounce::class => [
            'only' => [
                'Add'
            ]
        ],
        \app\api\middleware\GeetestCheck::class => [
            'only' => [
                'Add'
            ]
        ],
    ];

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
                $DbData['time'] = $this->attrGReqTime;
                $DbData['ip'] = $this->attrGReqIp;
                if (!Db::table('cards')->where('id', $DbData['cid'])->find()) {
                    return FunResult(false, 'CID不存在');
                }
                //默认状态ON/OFF:0/1
                $DbData['status'] = Config::get('lovecards.api.CardsComments.DefSetCardsCommentsStatus');
                //写入并返回ID
                $Id = $DbResult->insertGetId($DbData);
                //更新comments视图字段
                Db::table('cards')->where('id', $DbData['cid'])->inc('comments')->update();
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
    public function Add()
    {
        $result = self::CAndU('', [
            'cid' => Request::param('cid'),
            'content' => Request::param('content'),
            'name' => Request::param('name')
        ], 'c');

        if ($result['status']) {
            if (Config::get('lovecards.api.CardsComments.DefSetCardsCommentsStatus')) {
                return Export::mObjectEasyCreate('', '添加成功,等待审核', 201);
            } else {
                return Export::mObjectEasyCreate('', '添加成功', 200);
            }
        } else {
            return Export::mObjectEasyCreate($result['msg'], '添加失败', 500);
        }
    }

    //编辑-POST
    public function Edit()
    {
        $result = self::CAndU(Request::param('id'), [
            'content' => Request::param('content'),
            'name' => Request::param('name'),
            'status' => Request::param('status')
        ], 'u');

        if ($result['status']) {
            return Export::mObjectEasyCreate('', '编辑成功', 200);
        } else {
            return Export::mObjectEasyCreate($result['msg'], '编辑失败', 500);
        }
    }

    //删除-POST
    public function Delete()
    {
        $id = Request::param('id');
        if (!$id) {
            return Export::mObjectEasyCreate(['id' => '缺少参数'], '删除失败', 400);
        }

        //获取数据库对象
        $result = Db::table('cards_comments')->where('id', $id);
        if (!$result->find()) {
            return Export::mObjectEasyCreate([], 'id不存在', 400);
        }
        if (!$result->delete()) {
            return Export::mObjectEasyCreate([], '删除失败', 400);
        }
        return Export::mObjectEasyCreate([], '删除成功', 200);
    }
}
