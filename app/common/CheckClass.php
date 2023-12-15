<?php
//该类仅供中间件使用
namespace app\common;

use app\common\Common;
use app\common\Export;
use app\common\BackEnd;
use app\common\FrontEnd;

use think\facade\Cookie;

class CheckClass
{

    //管理员全部数据
    var $attrLDefAdminAllData;

    //API使用

    /**
     * 从JwtAuthCheck的中间件中取出Jwt解码用户数据
     * 通过解码出的AID查询admin_id行的数据
     *
     * @return void|object 
     */
    public function mObjectGetNowAdminAllData()
    {
        $TDef_JwtData = request()->JwtData;
        //验证身份并返回数据
        $this->attrLDefAdminAllData = BackEnd::mArrayGetNowAdminAllData($TDef_JwtData['aid']);

        if (!$this->attrLDefAdminAllData['status']) {
            return Export::Create(null, 401, $this->attrLDefAdminAllData['msg']);
        }
        $this->attrLDefAdminAllData = $this->attrLDefAdminAllData['data'];
    }

    /**
     * 权限校验 失败返回API对象
     *
     * @return void|object
     */
    public function mObjectEasyVerifyPower()
    {
        //权限验证
        if ($this->attrLDefAdminAllData['power'] != 0) {
            return Export::Create(['power' => 1], 401, '权限不足');
        }
    }

    /**
     * 从Cookie中取出Token
     * 验证失败并返回重定向对象
     *
     * @return void|object
     */
    public function mArrayGetNowAdminAllData()
    {
        //通过Cookie的TOKEN验证身份并返回数据
        $TDef_AdminAllData = FrontEnd::mResultGetNowAdminAllData();
        if (!$TDef_AdminAllData['status']) {
            //身份获取失败跳转并提醒
            //Cookie::delete('TOKEN'); //清除token并重定向
            return FrontEnd::mObjectEasyFrontEndJumpUrl('/admin/login', $TDef_AdminAllData['msg']);
        }
        $this->attrLDefAdminAllData = $TDef_AdminAllData['data'];
    }

    /**
     * 权限校验 失败返回重定向对象
     *
     * @return void($this->attrLDefAdminAllData)|object
     */
    public function mArrayEasyVerifyPower()
    {
        //权限验证
        if ($this->attrLDefAdminAllData['power'] != 0) {
            return FrontEnd::mObjectEasyFrontEndJumpUrl('/admin', '权限不足');
        }
    }
}
