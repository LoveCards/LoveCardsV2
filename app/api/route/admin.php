<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;

use app\api\middleware\PermissionCheck;

Route::group('', function () {
    //超管
    Route::get('system/updata', 'admin.System/updata');

    Route::get('system/themes', 'admin.System/themes');

    Route::get('system/config', 'admin.System/config');
    Route::post('system/config', 'admin.System/setConfig');

    Route::post('system/site', 'admin.System/Site');

    //Route::get('system/email', 'admin.System/GetEmail');
    Route::rule('system/email', 'admin.System/Email', 'PUT|PATCH');

    //Route::get('system/other', 'admin.System/GetOther');
    //Route::rule('system/other', 'admin.System/Other', 'PUT|PATCH');

    Route::post('system/set-theme', 'admin.System/themeSet');
    Route::post('system/theme-config', 'admin.System/themeConfig');
    //Route::post('system/geetest', 'admin.System/Geetest');

    Route::post('cards/setting', 'admin.Cards/Setting');

    //管理员
    Route::get('admin/card', 'admin.Cards/Get');
    Route::get('admin/cards', 'admin.Cards/Index');
    Route::patch('admin/card', 'admin.Cards/Patch');
    Route::delete('admin/cards', 'admin.Cards/Delete');
    Route::post('admin/cards/batch-operate', 'admin.Cards/BatchOperate');

    Route::get('admin/users', 'admin.Users/Index');
    Route::patch('admin/user', 'admin.Users/Patch');
    Route::delete('admin/user', 'admin.Users/Delete');
    Route::post('admin/users/batch-operate', 'admin.Users/BatchOperate');

    Route::get('admin/tags', 'admin.Tags/Index');
    Route::post('admin/tag', 'admin.Tags/Create');
    Route::patch('admin/tag', 'admin.Tags/Patch');
    Route::delete('admin/tag', 'admin.Tags/Delete');
    Route::post('admin/tags/batch-operate', 'admin.Tags/BatchOperate');

    Route::get('admin/comments', 'admin.Comments/Index');
    Route::patch('admin/comment', 'admin.Comments/Patch');
    Route::delete('admin/comment', 'admin.Comments/Delete');
    Route::post('admin/comments/batch-operate', 'admin.Comments/BatchOperate');

    //角色管理
    Route::get('admin/roles', 'admin.Roles/Index');
    Route::get('admin/role/permissions', 'admin.Roles/GetRolePermissions');
    Route::get('admin/role', 'admin.Roles/Get');
    Route::post('admin/role/assign-permissions', 'admin.Roles/AssignPermissions');
    Route::post('admin/role', 'admin.Roles/Create');
    Route::patch('admin/role', 'admin.Roles/Patch');
    Route::delete('admin/role', 'admin.Roles/Delete');

    //权限管理
    Route::get('admin/permissions/all', 'admin.Permissions/All');
    Route::get('admin/permissions', 'admin.Permissions/Index');
    Route::get('admin/permission', 'admin.Permissions/Get');
    Route::post('admin/permission', 'admin.Permissions/Create');
    Route::patch('admin/permission', 'admin.Permissions/Patch');
    Route::delete('admin/permission', 'admin.Permissions/Delete');

    //角色权限关联
    Route::post('admin/role-permission', 'admin.RolePermissions/Add');
    Route::delete('admin/role-permission', 'admin.RolePermissions/Remove');
    Route::post('admin/role-permissions/batch-add', 'admin.RolePermissions/BatchAdd');
    Route::post('admin/role-permissions/batch-remove', 'admin.RolePermissions/BatchRemove');

    //控制台
    Route::get('admin/dashboard', 'admin.Dashboard/Index');
})->middleware([JwtAuthCheck::class, PermissionCheck::class]);
