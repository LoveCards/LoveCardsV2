<?php

namespace app\api\controller;

use think\facade\Config;
use think\facade\Filesystem;
use app\common\Base;
use app\common\Export;

use think\facade\Request;

use app\api\model\Images as ImagesModel;
use app\api\validate\Upload as UploadValidate;
use think\exception\ValidateException;
use app\common\Base as CommonBase;

class Upload
{
    //上传图片-POST 这个api要替换掉的
    public function Image()
    {
        if (empty(request()->file('file'))) {
            return Export::mObjectEasyCreate([], '请提交文件', 400);
        }
        // 获取表单上传文件
        $file = request()->file('file');
        $DefSetCardsImgSize = Config::get('lovecards.api.Upload.DefSetCardsImgSize');

        $lDef_ImageExt = CommonBase::conf()->mArraySearchConfigKey('UserImageExt');
        $lDef_ImageExt[0] != '' ? $lDef_ImageExt = $lDef_ImageExt[0] : $lDef_ImageExt = 'jpg,png,gif';
        //验证
        try {
            validate(['file' => [     //file是你自定义的键名，目的是为了对check里数组中的
                'fileSize' => 1024 * 1000 * $DefSetCardsImgSize, //允许文件大小
                'fileExt'  => $lDef_ImageExt,  //文件后缀
                //'fileMime' => array('jpg', 'png'),  //文件类型
            ]])->check(['file' => $file]); //对上传的$file进行验证

            $saveName = \think\facade\Filesystem::disk('public')->putFile('image', $file); //保存文件名

            return Export::mObjectEasyCreate($saveName, '上传成功', 200);
        } catch (\Exception $e) {
            return Export::mObjectEasyCreate($e->getMessage(), '上传失败', 400);
        }
    }

    //上传文件-POST
    public function UserImages()
    {
        $context = request()->JwtData;

        $lReq_ParmasArray = [
            'file' => request()->file('file'),
            'aid' => Request::param('aid'),
            'pid' => Request::param('pid'),
            'user_id' => Request::param('user_id'),
        ];

        //校验参数
        try {
            validate(UploadValidate::class)
                ->scene('CheckUpload')
                ->check($lReq_ParmasArray);
        } catch (\Exception $e) {
            return Export::Create($e->getMessage(), 500, '上传失败');
        }

        //保存图片
        $lDef_Result = Filesystem::disk('public')->putFile('image', $lReq_ParmasArray['file']);
        if (!$lDef_Result) {
            return Export::Create('保存文件失败', 500, '上传失败');
        }

        //创建数据
        if (!isset($context['aid'])) {
            $lReq_ParmasArray['uid'] = $context['uid'];
        }
        $lReq_ParmasArray['url'] =  Request::scheme() . '://' . Request::host() . '/storage/' . $lDef_Result;
        $lDef_CreatData = ImagesModel::create($lReq_ParmasArray);
        if (!$lDef_CreatData) {
            //待完善-回滚操作，删除文件
            return Export::Create('数据创建失败', 500, '上传失败');
        }

        return Export::Create(['id' => $lDef_CreatData->id, 'url' => $lReq_ParmasArray['url']], 200, null);
    }
}
