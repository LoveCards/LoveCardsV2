<?php

namespace app\api\controller;

//TP请求类
use think\facade\Request;
//TP验证类
use think\exception\ValidateException;
//TPDb类
use think\facade\Db;

//Cards验证类
use app\api\validate\Cards as CardsValidate;
//公共类
use app\Common\Common;


class Cards
{
    //添加-POST
    public function add()
    {
        //获取数据
        $content = Request::param('content');

        $woName = Request::param('woName');
        $woContact = Request::param('woContact');
        $taName = Request::param('taName');
        $taContact = Request::param('taContact');

        $img = json_decode(Request::param('img'), true);
        $tag = json_decode(Request::param('tag'), true);

        $model = 0;
        $state = 0;
        //免验证
        $time = date('Y-m-d H:i:s');
        $ip = Common::getIp();

        //验证参数是否合法
        try {
            validate(CardsValidate::class)->batch(true)
                ->check([
                    'content' => $content,

                    'woName' => $woName,
                    'woContact' => $woContact,
                    'taName' => $taName,
                    'taContact' => $taContact,

                    'model' => $model,
                    'state' => $state
                ]);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $cardsvalidateerror = $e->getError();
            return Common::create($cardsvalidateerror, '添加失败', 400);
        }
        //获取Cards数据库对象
        $result = Db::table('cards');
        //判断卡模式
        $data = array(); //清空数组
        if ($model == 0) {
            //表白卡模式
            $data = [
                'content' => $content,

                'woName' => $woName,
                'woContact' => $woContact,
                'taName' => $taName,
                'taContact' => $taContact,

                'model' => $model,
                'state' => $state,

                'time' => $time,
                'ip' => $ip,
            ];
        } else {
            //留言卡模式
            $data = [
                'content' => $content,

                'woName' => $woName,
                'woContact' => $woContact,

                'model' => $model,
                'state' => $state,

                'time' => $time,
                'ip' => $ip,
            ];
        }
        //Cards写入库
        $resultCardId = $result->insertGetId($data);

        if (!empty($img)) {
            //获取img数据库对象
            $result = Db::table('img');
            //构建数据数组
            $data = array(); //清空数组
            foreach ($img as $key => $value) {
                $data[$key]['aid'] = 1;
                $data[$key]['pid'] = $resultCardId;
                $data[$key]['url'] = $value;
                $data[$key]['time'] = $time;
            }
            //写入img数据库
            $result->insertAll($data);
        }

        if (!empty($tag)) {
            //获取tag_map数据库对象
            $result = Db::table('cards_tag_map');
            //构建数据数组
            $data = array(); //清空数组
            foreach ($tag as $key => $value) {
                $data[$key]['cid'] = $resultCardId;
                $data[$key]['tid'] = $value;
                $data[$key]['time'] = $time;
            }
            //写入tag_map数据库
            $result->insertAll($data);
        }
        //返回数据
        return Common::create(['id' => $resultCardId], '添加成功', 200);
    }

    //编辑-POST
    public function edit()
    {

        //验证身份并返回数据
        $userData = Common::validateAuth();
        if (!empty($userData[0])) {
            return Common::create([], $userData[1], $userData[0]);
        }

        //获取数据
        $id = Request::param('id');
        $content = Request::param('content');

        $woName = Request::param('woName');
        $woContact = Request::param('woContact');
        $taName = Request::param('taName');
        $taContact = Request::param('taContact');

        $img = json_decode(Request::param('img'), true);
        $tag = json_decode(Request::param('tag'), true);

        $model = Request::param('model');
        $state = Request::param('state');
        //免验证
        $time = date('Y-m-d H:i:s');
        $ip = Common::getIp();

        //验证参数是否合法
        try {
            validate(CardsValidate::class)->batch(true)
                ->check([
                    'content' => $content,

                    'woName' => $woName,
                    'woContact' => $woContact,
                    'taName' => $taName,
                    'taContact' => $taContact,

                    'model' => $model,
                    'state' => $state
                ]);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $cardsvalidateerror = $e->getError();
            return Common::create($cardsvalidateerror, '添加失败', 400);
        }
        //获取Cards数据库对象
        $result = Db::table('cards')->where('id', $id);
        if (!$result->find()) {
            return Common::create([], 'id不存在', 400);
        }

        //判断卡模式
        $data = array(); //清空数组
        if ($model == 0) {
            //表白卡模式
            $data = [
                'content' => $content,

                'woName' => $woName,
                'woContact' => $woContact,
                'taName' => $taName,
                'taContact' => $taContact,

                'model' => $model,
                'state' => $state,

                'time' => $time,
                'ip' => $ip,
            ];
        } else {
            //留言卡模式
            $data = [
                'content' => $content,

                'woName' => $woName,
                'woContact' => $woContact,

                'model' => $model,
                'state' => $state,

                'time' => $time,
                'ip' => $ip,
            ];
        }
        //Cards写入库
        $result->update($data);

        //获取img数据库对象
        $result = Db::table('img');
        //清理原始数据
        $resultId = $result->where('pid', $id);
        if ($resultId->find()) {
            $resultId->delete();
        }
        //判断是否写入新数据
        if (!empty($img)) {
            //清空数组
            $data = array();
            //构建数据数组
            foreach ($img as $key => $value) {
                $data[$key]['aid'] = 1;
                $data[$key]['pid'] = $id;
                $data[$key]['url'] = $value;
                $data[$key]['time'] = $time;
            }
            //写入数据库
            $result->insertAll($data);
        }

        //获取tag数据库对象
        $result = Db::table('cards_tag_map');
        //清理原始数据
        $resultId = $result->where('cid', $id);
        if ($resultId->find()) {
            $resultId->delete();
        }
        //判断是否写入新数据
        if (!empty($tag)) {
            //清空数组
            $data = array();
            //构建数据数组
            foreach ($tag as $key => $value) {
                $data[$key]['cid'] = $id;
                $data[$key]['tid'] = $value;
                $data[$key]['time'] = $time;
            }
            //写入tag_map数据库
            $result->insertAll($data);
        }

        //返回数据
        return Common::create(['id' => $id], '编辑成功', 200);
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
}
