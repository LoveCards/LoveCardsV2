<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

// 角色管理（管理员）
Route::group('all/roles', function () {
    Route::get('', 'Rbac.Roles/list')->name('roles.list')->setOption('meta', ['name' => '角色列表', 'group' => '角色']);
    Route::get(':id/permissions', 'Rbac.Roles/getRolePermissions')->name('roles.getRolePermissions')->setOption('meta', ['name' => '获取角色权限', 'group' => '角色']);
    Route::get(':id', 'Rbac.Roles/get')->name('roles.get')->setOption('meta', ['name' => '角色详情', 'group' => '角色']);
    Route::post('', 'Rbac.Roles/create')->name('roles.create')->setOption('meta', ['name' => '创建角色', 'group' => '角色']);
    Route::post('reseed', 'Rbac.Roles/reseed')->name('roles.reseed')->setOption('meta', ['name' => '重新 seed 权限', 'group' => '角色']);
    Route::post(':id/permissions', 'Rbac.Roles/assignPermissions')->name('roles.assignPermissions')->setOption('meta', ['name' => '分配权限', 'group' => '角色']);
    Route::patch(':id', 'Rbac.Roles/update')->name('roles.update')->setOption('meta', ['name' => '编辑角色', 'group' => '角色']);
    Route::delete(':id', 'Rbac.Roles/delete')->name('roles.delete')->setOption('meta', ['name' => '删除角色', 'group' => '角色']);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
