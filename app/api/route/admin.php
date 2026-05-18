<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;

use app\api\middleware\PermissionCheck;

Route::group('', function () {
    //配置管理
    Route::get('system/config', 'admin.Config/index')->name('system.config.index');
    Route::post('system/config', 'admin.Config/save')->name('system.config.save');

    //存储渠道管理
    Route::get('storage/channels', 'admin.Config/storageChannels')->name('storage.channels.index');
    Route::get('storage/channels/stats', 'admin.Config/channelStats')->name('storage.channels.stats');
    Route::post('storage/channels/{channel}/test', 'admin.Config/testChannel')->name('storage.channels.test');

    //主题管理（保留）
    Route::get('system/updata', 'admin.System/updata')->name('system.update');
    Route::get('system/themes', 'admin.System/themes')->name('system.themes');
    Route::post('system/set-theme', 'admin.System/themeSet')->name('system.set-theme');
    Route::post('system/theme-config', 'admin.System/themeConfig')->name('system.theme-config');

    Route::post('cards/setting', 'admin.Cards/Setting')->name('cards.setting');

    //管理员
    Route::get('admin/card', 'admin.Cards/Get')->name('admin.cards.show');
    Route::get('admin/cards', 'admin.Cards/Index')->name('admin.cards.index');
    Route::patch('admin/card', 'admin.Cards/Patch')->name('admin.cards.update');
    Route::delete('admin/cards', 'admin.Cards/Delete')->name('admin.cards.destroy');
    Route::post('admin/cards/batch-operate', 'admin.Cards/BatchOperate')->name('admin.cards.batch');

    Route::get('admin/users', 'admin.Users/Index')->name('admin.users.index');
    Route::patch('admin/user', 'admin.Users/Patch')->name('admin.users.update');
    Route::delete('admin/user', 'admin.Users/Delete')->name('admin.users.destroy');
    Route::post('admin/users/batch-operate', 'admin.Users/BatchOperate')->name('admin.users.batch');

    Route::get('admin/tags', 'admin.Tags/Index')->name('admin.tags.index');
    Route::post('admin/tag', 'admin.Tags/Create')->name('admin.tags.store');
    Route::patch('admin/tag', 'admin.Tags/Patch')->name('admin.tags.update');
    Route::delete('admin/tag', 'admin.Tags/Delete')->name('admin.tags.destroy');
    Route::post('admin/tags/batch-operate', 'admin.Tags/BatchOperate')->name('admin.tags.batch');

    Route::get('admin/comments', 'admin.Comments/Index')->name('admin.comments.index');
    Route::patch('admin/comment', 'admin.Comments/Patch')->name('admin.comments.update');
    Route::delete('admin/comment', 'admin.Comments/Delete')->name('admin.comments.destroy');
    Route::post('admin/comments/batch-operate', 'admin.Comments/BatchOperate')->name('admin.comments.batch');

    //角色管理
    Route::get('admin/roles', 'admin.Roles/Index')->name('admin.roles.index');
    Route::get('admin/role/permissions', 'admin.Roles/GetRolePermissions')->name('admin.roles.permissions');
    Route::get('admin/role', 'admin.Roles/Get')->name('admin.roles.show');
    Route::post('admin/role/assign-permissions', 'admin.Roles/AssignPermissions')->name('admin.roles.assign');
    Route::post('admin/role', 'admin.Roles/Create')->name('admin.roles.store');
    Route::patch('admin/role', 'admin.Roles/Patch')->name('admin.roles.update');
    Route::delete('admin/role', 'admin.Roles/Delete')->name('admin.roles.destroy');

    //权限管理
    Route::get('admin/permissions/all', 'admin.Permissions/All')->name('admin.permissions.all');
    Route::get('admin/permissions', 'admin.Permissions/Index')->name('admin.permissions.index');
    Route::get('admin/permission', 'admin.Permissions/Get')->name('admin.permissions.show');
    Route::post('admin/permission', 'admin.Permissions/Create')->name('admin.permissions.store');
    Route::patch('admin/permission', 'admin.Permissions/Patch')->name('admin.permissions.update');
    Route::delete('admin/permission', 'admin.Permissions/Delete')->name('admin.permissions.destroy');

    //角色权限关联
    Route::post('admin/role-permission', 'admin.RolePermissions/Add')->name('admin.role-permissions.store');
    Route::delete('admin/role-permission', 'admin.RolePermissions/Remove')->name('admin.role-permissions.destroy');
    Route::post('admin/role-permissions/batch-add', 'admin.RolePermissions/BatchAdd')->name('admin.role-permissions.batch-store');
    Route::post('admin/role-permissions/batch-remove', 'admin.RolePermissions/BatchRemove')->name('admin.role-permissions.batch-destroy');

    //控制台
    Route::get('admin/dashboard', 'admin.Dashboard/Index')->name('admin.dashboard');
})->middleware([JwtAuthCheck::class, PermissionCheck::class]);
