<?php

namespace app\api\controller;

//TP类
use think\facade\Db;
use think\facade\Request;
use think\exception\ValidateException;

//验证类
use app\api\validate\CardsComments as CommentsValidate;

//类
use app\Common\Common;


class CardsComments
{

    //默认评论状态ON/OFF:0/1
    const DefSetCardsCommentsState = 0;
    //默认添加评论上传图片个数
    const DefCardsSetCommentsImgNum = 3;

    //添加-POST
    public function add()
    {
        //防手抖
        $preventClicks = Common::preventClicks();
        if($preventClicks[0] == false){
            //返回数据
            return Common::create(['prompt' => $preventClicks[1]], '添加失败', 500);
        }

        $cid = Request::param('cid');
        if (!$cid) {
            return Common::create(['cid' => '缺少参数'], '添加失败', 400);
        }

        $name = Request::param('name');
        $content = Request::param('content');

        $time = date('Y-m-d H:i:s');
        $ip = Common::getIp();
        $state = 0; //上/下:0/1

        //验证参数是否合法
        try {
            validate(CommentsValidate::class)->batch(true)
                ->check([
                    'content' => $content,
                    'name' => $name,
                    'state' => $state
                ]);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $Commentsvalidateerror = $e->getError();
            return Common::create($Commentsvalidateerror, '添加失败', 400);
        }

        //获取数据库对象
        $result = Db::table('cards_comments');
        //整理数据
        $data = ['cid' => $cid, 'content' => $content, 'name' => $name,  'state' => $state, 'ip' => $ip, 'time' => $time];
        //写入失败返回
        if (!$result->insert($data)) {
            return Common::create(['CardsComments' => '写入失败'], '添加失败', 400);
        }

        //更新视图字段
        $result = Db::table('cards')->where('id', $cid);
        if (!$result->inc('comments')->update()) {
            return Common::create(['cards.comments' => 'cards.comments更新失败'], '添加成功', 400);
        };
        //返回结果
        return Common::create([], '添加成功', 200);
    }

    //编辑-POST
    public function edit()
    {
        //验证身份并返回数据
        $userData = Common::validateAuth();
        if (!empty($userData[0])) {
            return Common::create([], $userData[1], $userData[0]);
        }

        $id = Request::param('id');
        if (!$id) {
            return Common::create(['id' => '缺少参数'], '编辑失败', 400);
        }

        $name = Request::param('name');
        $content = Request::param('content');

        $state =  Request::param('state'); //上/下:0/1

        //验证参数是否合法
        try {
            validate(CommentsValidate::class)->batch(true)
                ->check([
                    'content' => $content,
                    'name' => $name,
                    'state' => $state
                ]);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $Commentsvalidateerror = $e->getError();
            return Common::create($Commentsvalidateerror, '编辑失败', 400);
        }

        //获取数据库对象
        $result = Db::table('cards_comments')->where('id', $id);
        if (!$result->find()) {
            return Common::create([], 'id不存在', 400);
        }

        //整理数据
        $data = ['content' => $content, 'name' => $name,  'state' => $state];
        //写入失败返回
        if (!$result->update($data)) {
            return Common::create(['CardsComments' => '写入失败'], '编辑失败', 400);
        }
        //返回结果
        return Common::create([], '编辑成功', 200);
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
