<?php

namespace yunarch\app\roles;

use think\facade\Request;

class Roles
{
    var $group = [
        0 => [
            'value' => 'root',
            'name' => '超级管理员',
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
                '/api/cards/index',
                '/api/cards/edit',
                '/api/cards/delete',

                '/api/comments/edit',
                '/api/comments/delete',

                '/api/tags/add',
                '/api/tags/edit',
                '/api/tags/delete',

                '/api/users/index',
                '/api/users/patch',
                '/api/users/delete',

                '/api/dashboard',
            ],
            'restful_api' => [
                [
                    'base_url' => '/api/cards',
                    'methods' => [
                        'GET',
                        'POST',
                        'PUT',
                        'DELETE'
                    ]
                ],
                [
                    'base_url' => '/api/comments',
                    'methods' => [
                        'GET',
                        'POST',
                        'PUT',
                        'DELETE'
                    ]
                ],
                [
                    'base_url' => '/api/tags',
                    'methods' => [
                        'GET',
                        'POST',
                        'PUT',
                        'DELETE'
                    ]
                ],
                [
                    'base_url' => '/api/users',
                    'methods' => [
                        'GET',
                        'POST',
                        'PUT',
                        'DELETE'
                    ]
                ],

            ]
        ],
        2 => [
            'value' => 'user',
            'name' => '用户',
            'baseUrl' => [
                '/api/upload/user-images',
                '/api/user/password',
                '/api/user/email',
                '/api/user/email-captcha',
                //'/api/user',
                '/api/cards',
                '/api/comments',
                '/api/likes',
                //游客api
                '/api/cards/add',
                '/api/comments/add',
                '/api/cards/good',

                //新-遵循restful api
                '/api/user/info',
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
        $currentUrl = strtolower(Request::baseUrl());

        if (in_array($currentUrl, $baseUrl)) {
            return true;
        }

        return false;
    }
}
