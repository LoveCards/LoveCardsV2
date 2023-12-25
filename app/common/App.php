<?php

namespace app\common;

use think\Facade;
use think\facade\Session;
use think\facade\Request;
use think\facade\Db;
use app\common\Common;

class App extends Facade
{

    //模块id与名
    protected static function mArrayGetAppTableIdMap(): array
    {
        return [
            0 => 'users',
            1 => 'cards',
            2 => 'comments',
        ];
    }

    //验证并返回id
    protected static function mArrayGetAppTableMapValue($tReq_Value): array
    {
        $tDef_AppTableIdMap = self::mArrayGetAppTableIdMap();
        if (is_numeric($tReq_Value)) {
            // 如果输入值是数字，直接根据映射返回相关信息
            $tReq_Value = (int) $tReq_Value; // 将字符串 '1' 转换为整数 1
            if (array_key_exists($tReq_Value, $tDef_AppTableIdMap)) {
                return Common::mArrayEasyReturnStruct('AppId', true, $tDef_AppTableIdMap[$tReq_Value]);
            }
            return Common::mArrayEasyReturnStruct('AppId', false);
        } elseif (is_string($tReq_Value)) {
            $tDef_AppTableNameMap = array_flip($tDef_AppTableIdMap);
            if (array_key_exists($tReq_Value, $tDef_AppTableNameMap)) {
                return Common::mArrayEasyReturnStruct('AppTableName', true, $tDef_AppTableNameMap[$tReq_Value]);
            }
            return Common::mArrayEasyReturnStruct('AppTableName', false);
        } else {
            return Common::mArrayEasyReturnStruct('null', false);
        }
    }

    //检查PID与AID是否存在
    protected static function mArrayCheckAidAndPid($tReq_Aid, $tReq_Pid): array
    {
        //获取并验证PID
        $tDef_AppTableMapGetValue = self::mArrayGetAppTableMapValue($tReq_Aid);
        if (!$tDef_AppTableMapGetValue['status']) {
            return Common::mArrayEasyReturnStruct('aid不存在', false);
        }
        $tDef_AppTableName = $tDef_AppTableMapGetValue['data'];

        //查询PID是否存在
        if (!Db::table($tDef_AppTableName)->where('id', $tReq_Pid)->find()) {
            return Common::mArrayEasyReturnStruct('PID对应CID不存在', false);
        }

        return Common::mArrayEasyReturnStruct('成功');
    }
}
