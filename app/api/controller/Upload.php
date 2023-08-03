<?php

namespace app\api\controller;

//TP
use think\facade\Config;

//公共
use app\Common\Common;
use app\api\common\Common as ApiCommon;

class Upload
{
    //上传图片-POST
    public function image()
    {
        if (empty(request()->file('file'))) {
            return Common::create([], '请提交文件', 400);
        }
        // 获取表单上传文件
        $file = request()->file('file');
        $DefSetCardsImgSize = Config::get('lovecards.api.Upload.DefSetCardsImgSize');
        //验证
        try {
            validate(['file' => [     //file是你自定义的键名，目的是为了对check里数组中的
                'fileSize' => 1024 * 1000 * $DefSetCardsImgSize, //允许文件大小
                'fileExt'  => array('jpg', 'png'),  //文件后缀
                //'fileMime' => array('jpg', 'png'),  //文件类型
            ]])->check(['file' => $file]); //对上传的$file进行验证

            $saveName = \think\facade\Filesystem::disk('public')->putFile('image', $file); //保存文件名

            return Common::create($saveName, '上传成功', 200);
        } catch (\Exception $e) {
            return Common::create($e->getMessage(), '上传失败', 400);
        }
    }
}
