<?php

namespace app\api\controller;

use think\facade\Request;
use think\facade\Db;
use think\exception\ValidateException;

use app\api\validate\CardsTag as TagValidate;

use app\common\Common;
use app\common\Export;

class CardsTag extends Common
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
                $DbData['time'] = $this->attrGReqTime;
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
        $result = self::CAndU('', [
            'tip' => Request::param('tip'),
            'name' => Request::param('name'),
        ], 'c');

        if ($result['status']) {
            return Export::mObjectEasyCreate('', '添加成功', 200);
        } else {
            return Export::mObjectEasyCreate($result['msg'], '添加失败', 500);
        }
    }

    //编辑-POST
    public function edit()
    {
        $result = self::CAndU(Request::param('id'), [
            'tip' => Request::param('tip'),
            'name' => Request::param('name'),
            'status' => Request::param('status'),
        ], 'u');

        if ($result['status']) {
            return Export::mObjectEasyCreate('', '编辑成功', 200);
        } else {
            return Export::mObjectEasyCreate($result['msg'], '编辑失败', 500);
        }
    }

    //删除-POST
    public function delete()
    {
        //获取数据
        $id = Request::param('id');

        //获取数据库对象
        $result = Db::table('cards_tag')->where('id', $id);
        if (!$result->find()) {
            return Export::mObjectEasyCreate([], 'id不存在', 400);
        }
        $result->delete();

        //获取tag_map数据库对象
        $result = Db::table('cards_tag_map')->where('tid', $id);
        if ($result->find()) {
            $result->delete();
        }

        //返回数据
        return Export::mObjectEasyCreate([], '删除成功', 200);
    }
}
