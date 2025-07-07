<?php

namespace yunarch\app\roles;

use think\facade\Request;

class Roles
{
    var $group = [
        0 => [
            'value' => 'root',
            'name' => '超级管理员',
            //待优化
            'baseUrl' => [
                '/api/cards/setting',
                '/api/system/site',

                '/api/system/email',
                '/api/system/email',

                '/api/system/other',
                '/api/system/other',

                '/api/system/template',
                '/api/system/templateset',
                '/api/system/geetest',
            ]
        ],
        1 => [
            'value' => 'admin',
            'name' => '管理员',
            'baseUrl' => [
                //User
                '/api/admin/users:get',
                '/api/admin/users:patch',
                '/api/admin/users:delete',

                //Cards
                '/api/admin/card:get',
                '/api/admin/cards:get',
                '/api/admin/card:patch',
                '/api/admin/cards:delete',
                '/api/admin/cards/batch-operate:post',

                //Comments
                '/api/admin/comments:get',
                '/api/admin/comment:patch',
                '/api/admin/comment:delete',
                '/api/admin/comments/batch-operate:post',

                //Tags
                '/api/admin/tags:get',
                '/api/admin/tag:post',
                '/api/admin/tag:patch',
                '/api/admin/tag:delete',
                '/api/admin/tags/batch-operate:post',

                //Dashboard
                '/api/admin/dashboard:get',
            ]
        ],
        2 => [
            'value' => 'user',
            'name' => '用户',
            //旧的API风格
            'baseUrl' => [
                '/api/upload/user-images:post',
                '/api/user/password:post',
                '/api/user/email:post',
                '/api/user/email-captcha:post',

                '/api/card/images:get',
                '/api/cards:get',
                '/api/cards:delete',

                '/api/comments:get',
                '/api/comments:delete',

                '/api/likes:get',
                '/api/likes:delete',
                //游客api
                '/api/cards/add:post',
                '/api/comments/add:post',
                '/api/cards/good:post',

                //新-遵循restful api
                '/api/user/info:patch',
                '/api/user/info:get',

                '/api/tags:get',
            ]
        ],
    ];

    public function getGroup()
    {
        return $this->group;
    }

    public function getGroupValue($value)
    {
        foreach ($this->group as $key => $val) {
            if ($val['value'] == $value) {
                return $val;
            }
        }
        return null;
    }

    public function checkIdBaseUrlPass($id)
    {
        // 如果传入的是数组，则遍历数组，检查每个 id 是否满足条件
        if (is_array($id)) {
            foreach ($id as $singleId) {
                if ($this->checkSingleIdBaseUrlPass($singleId)) {
                    return true; // 只要有一个 id 满足条件，就返回 true
                }
            }
            return false; // 如果遍历完数组都没有满足条件，返回 false
        } else {
            // 如果传入的是单个 id，直接调用 checkSingleBaseUrlPass 方法检查
            return $this->checkSingleIdBaseUrlPass($id);
        }
    }

    public function checkSingleIdBaseUrlPass($id)
    {
        $group = $this->getGroup();
        if (!isset($group[$id])) {
            return false; // 如果 id 不存在于用户组中，直接返回 false
        }

        $baseUrl = $group[$id]['baseUrl'];
        //url+方法
        $currentUrl = strtolower(Request::baseUrl() . ':' . Request::method());

        if (in_array($currentUrl, $baseUrl)) {
            return true;
        }

        return false;
    }
}
