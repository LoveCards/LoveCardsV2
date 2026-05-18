<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;

use app\api\middleware\PermissionCheck;

Route::group('', function () {
    //配置管理
    Route::get('system/config', 'admin.Config/index')
        ->name('system.config.index')
        ->setOption('meta', ['name' => '获取系统配置', 'group' => 'system.config']);

    Route::post('system/config', 'admin.Config/save')
        ->name('system.config.save')
        ->setOption('meta', ['name' => '保存系统配置', 'group' => 'system.config']);

    //存储渠道管理
    Route::get('storage/channels', 'admin.Config/storageChannels')
        ->name('storage.channels.index')
        ->setOption('meta', ['name' => '获取存储渠道列表', 'group' => 'storage.channels']);

    Route::get('storage/channels/stats', 'admin.Config/channelStats')
        ->name('storage.channels.stats')
        ->setOption('meta', ['name' => '获取渠道统计', 'group' => 'storage.channels']);

    Route::post('storage/channels/{channel}/test', 'admin.Config/testChannel')
        ->name('storage.channels.test')
        ->setOption('meta', ['name' => '测试存储渠道', 'group' => 'storage.channels']);

    //主题管理
    Route::get('system/updata', 'admin.System/updata')
        ->name('system.update')
        ->setOption('meta', ['name' => '系统更新检查', 'group' => 'system']);

    Route::get('system/themes', 'admin.System/themes')
        ->name('system.themes')
        ->setOption('meta', ['name' => '获取主题列表', 'group' => 'system']);

    Route::post('system/set-theme', 'admin.System/themeSet')
        ->name('system.set-theme')
        ->setOption('meta', ['name' => '设置主题', 'group' => 'system']);

    Route::post('system/theme-config', 'admin.System/themeConfig')
        ->name('system.theme-config')
        ->setOption('meta', ['name' => '设置主题配置', 'group' => 'system']);

    Route::post('cards/setting', 'admin.Cards/Setting')
        ->name('cards.setting')
        ->setOption('meta', ['name' => '卡片设置', 'group' => 'cards']);

    //卡片管理
    Route::get('admin/card', 'admin.Cards/Get')
        ->name('admin.cards.show')
        ->setOption('meta', ['name' => '获取单个卡片', 'group' => 'admin.cards']);

    Route::get('admin/cards', 'admin.Cards/Index')
        ->name('admin.cards.index')
        ->setOption('meta', ['name' => '获取卡片列表', 'group' => 'admin.cards']);

    Route::patch('admin/card', 'admin.Cards/Patch')
        ->name('admin.cards.update')
        ->setOption('meta', ['name' => '更新卡片', 'group' => 'admin.cards']);

    Route::delete('admin/cards', 'admin.Cards/Delete')
        ->name('admin.cards.destroy')
        ->setOption('meta', ['name' => '删除卡片', 'group' => 'admin.cards']);

    Route::post('admin/cards/batch-operate', 'admin.Cards/BatchOperate')
        ->name('admin.cards.batch')
        ->setOption('meta', ['name' => '批量操作卡片', 'group' => 'admin.cards']);

    //用户管理
    Route::get('admin/users', 'admin.Users/Index')
        ->name('admin.users.index')
        ->setOption('meta', ['name' => '获取用户列表', 'group' => 'admin.users']);

    Route::patch('admin/user', 'admin.Users/Patch')
        ->name('admin.users.update')
        ->setOption('meta', ['name' => '更新用户', 'group' => 'admin.users']);

    Route::delete('admin/user', 'admin.Users/Delete')
        ->name('admin.users.destroy')
        ->setOption('meta', ['name' => '删除用户', 'group' => 'admin.users']);

    Route::post('admin/users/batch-operate', 'admin.Users/BatchOperate')
        ->name('admin.users.batch')
        ->setOption('meta', ['name' => '批量操作用户', 'group' => 'admin.users']);

    //标签管理
    Route::get('admin/tags', 'admin.Tags/Index')
        ->name('admin.tags.index')
        ->setOption('meta', ['name' => '获取标签列表', 'group' => 'admin.tags']);

    Route::post('admin/tag', 'admin.Tags/Create')
        ->name('admin.tags.store')
        ->setOption('meta', ['name' => '创建标签', 'group' => 'admin.tags']);

    Route::patch('admin/tag', 'admin.Tags/Patch')
        ->name('admin.tags.update')
        ->setOption('meta', ['name' => '更新标签', 'group' => 'admin.tags']);

    Route::delete('admin/tag', 'admin.Tags/Delete')
        ->name('admin.tags.destroy')
        ->setOption('meta', ['name' => '删除标签', 'group' => 'admin.tags']);

    Route::post('admin/tags/batch-operate', 'admin.Tags/BatchOperate')
        ->name('admin.tags.batch')
        ->setOption('meta', ['name' => '批量操作标签', 'group' => 'admin.tags']);

    //评论管理
    Route::get('admin/comments', 'admin.Comments/Index')
        ->name('admin.comments.index')
        ->setOption('meta', ['name' => '获取评论列表', 'group' => 'admin.comments']);

    Route::patch('admin/comment', 'admin.Comments/Patch')
        ->name('admin.comments.update')
        ->setOption('meta', ['name' => '更新评论', 'group' => 'admin.comments']);

    Route::delete('admin/comment', 'admin.Comments/Delete')
        ->name('admin.comments.destroy')
        ->setOption('meta', ['name' => '删除评论', 'group' => 'admin.comments']);

    Route::post('admin/comments/batch-operate', 'admin.Comments/BatchOperate')
        ->name('admin.comments.batch')
        ->setOption('meta', ['name' => '批量操作评论', 'group' => 'admin.comments']);

    //角色管理
    Route::get('admin/roles', 'admin.Roles/Index')
        ->name('admin.roles.index')
        ->setOption('meta', ['name' => '获取角色列表', 'group' => 'admin.roles']);

    Route::get('admin/role/permissions', 'admin.Roles/GetRolePermissions')
        ->name('admin.roles.permissions')
        ->setOption('meta', ['name' => '获取角色权限', 'group' => 'admin.roles']);

    Route::get('admin/role', 'admin.Roles/Get')
        ->name('admin.roles.show')
        ->setOption('meta', ['name' => '获取单个角色', 'group' => 'admin.roles']);

    Route::post('admin/role/assign-permissions', 'admin.Roles/AssignPermissions')
        ->name('admin.roles.assign')
        ->setOption('meta', ['name' => '分配权限', 'group' => 'admin.roles']);

    Route::post('admin/role', 'admin.Roles/Create')
        ->name('admin.roles.store')
        ->setOption('meta', ['name' => '创建角色', 'group' => 'admin.roles']);

    Route::patch('admin/role', 'admin.Roles/Patch')
        ->name('admin.roles.update')
        ->setOption('meta', ['name' => '更新角色', 'group' => 'admin.roles']);

    Route::delete('admin/role', 'admin.Roles/Delete')
        ->name('admin.roles.destroy')
        ->setOption('meta', ['name' => '删除角色', 'group' => 'admin.roles']);

    //权限管理（只读，从路由扫描）
    Route::get('admin/permissions/all', 'admin.Permissions/All')
        ->name('admin.permissions.all')
        ->setOption('meta', ['name' => '获取所有权限', 'group' => 'admin.permissions']);

    Route::get('admin/permissions', 'admin.Permissions/Index')
        ->name('admin.permissions.index')
        ->setOption('meta', ['name' => '权限列表', 'group' => 'admin.permissions']);

    //控制台
    Route::get('admin/dashboard', 'admin.Dashboard/Index')
        ->name('admin.dashboard')
        ->setOption('meta', ['name' => '控制台', 'group' => 'admin.dashboard']);
})->middleware([JwtAuthCheck::class, PermissionCheck::class]);
