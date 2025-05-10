<?php

namespace app\api\controller\admin;

use think\facade\Request;
use think\facade\Db;
use think\exception\ValidateException;

use app\api\model\Tags as TagsModel;
use app\api\service\Tags as TagsService;
use app\api\model\TagsMap as TagsMapModel;
use app\api\validate\Tags as TagsValidate;

use app\common\Common;
use app\common\Export;

use yunarch\app\api\controller\Utils as ApiControllerUtils;
use yunarch\app\api\controller\IndexUtils as ApiControllerIndexUtils;
use yunarch\app\api\validate\Index as ApiIndexValidate;

class Tags extends Common
{

    //获取-GET
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
        $lDef_Result = TagsService::Index($params);
        //返回结果
        return Export::Create($lDef_Result['data'], 200, null);
    }

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
            if ($method == 'c') {
                validate(TagsValidate::class)
                    ->batch(true)
                    ->check($Datas);
            } else {
                validate(TagsValidate::class)
                    ->remove('aid', 'require')
                    ->batch(true)
                    ->check($Datas);
            }
        } catch (ValidateException $e) {
            $validateerror = $e->getError();
            return Common::mArrayEasyReturnStruct('格式错误', false, $validateerror);
        }

        // 启动事务
        DB::startTrans();
        try {
            //获取数据库对象
            $DbResult = new TagsModel();
            $DbData = $Datas;
            // 方法选择
            if ($method == 'c') {
                $DbData['time'] = $this->attrGReqTime;
                //默认状态:0/1
                $DbData['status'] = 0;
                //写入并返回ID
                $DbResult->save($DbData);
                $Id = $DbResult->id;
            } else {
                //获取数据库对象
                $DbResult = TagsModel::find($id);
                if (!$DbResult->findOrEmpty()) {
                    return Common::mArrayEasyReturnStruct('ID不存在', false);
                }
                //写入并返回ID
                $DbResult->save($DbData);
            }

            // 提交事务
            DB::commit();
            return Common::mArrayEasyReturnStruct('操作成功');
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollback();
            return Common::mArrayEasyReturnStruct('操作失败', false, $e);
        }
    }

    //添加-POST
    public function Post()
    {
        $result = self::CAndU('', [
            'aid' => Request::param('aid'),
            'tip' => Request::param('tip'),
            'name' => Request::param('name'),
        ], 'c');

        if ($result['status']) {
            return Export::Create(null, 200);
        } else {
            return Export::Create($result['data'], 500, $result['msg']);
        }
    }

    //编辑-Patch
    public function Patch()
    {
        $result = self::CAndU(Request::param('id'), [
            'aid' => Request::param('aid'),
            'tip' => Request::param('tip'),
            'name' => Request::param('name'),
            'status' => Request::param('status'),
        ], 'u');

        if ($result['status']) {
            return Export::Create(null, 200);
        } else {
            return Export::Create($result['data'], 500, $result['msg']);
        }
    }

    //删除-DELETE
    public function Delete()
    {
        //获取数据
        $id = Request::param('id');

        //获取数据库对象
        $result = Db::table('tags')->where('id', $id);
        if (!$result->find()) {
            return Export::Create(null, 400, 'id不存在');
        }
        $result->delete();

        //获取tag_map数据库对象
        $result = Db::table('tags_map')->where('tid', $id);
        if ($result->find()) {
            $result->delete();
        }

        //返回数据
        return Export::Create(null, 200);
    }
}
