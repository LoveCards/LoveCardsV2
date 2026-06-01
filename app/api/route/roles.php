<?php

use think\facade\Route;

use app\api\middleware\JwtAuthCheck;
use app\api\middleware\PermissionCheck;

Route::group('roles', function () {
    Route::get('', 'Rbac.Roles/list')
        ->name('roles.list')
        ->setOption('meta', ['name' => '角色列表', 'group' => '角色', 'caps' => ['roles.read']]);

    Route::get(':id/capabilities', 'Rbac.Roles/getRoleCapabilities')
        ->name('roles.getRoleCapabilities')
        ->setOption('meta', ['name' => '获取角色能力', 'group' => '角色', 'caps' => ['roles.read']]);

    Route::get(':id', 'Rbac.Roles/get')
        ->name('roles.get')
        ->setOption('meta', ['name' => '角色详情', 'group' => '角色', 'caps' => ['roles.read']]);

    Route::post('', 'Rbac.Roles/create')
        ->name('roles.create')
        ->setOption('meta', ['name' => '创建角色', 'group' => '角色', 'caps' => ['roles.create']]);

    Route::post('reseed', 'Rbac.Roles/reseed')
        ->name('roles.reseed')
        ->setOption('meta', ['name' => '重新 seed 能力', 'group' => '角色', 'caps' => ['roles.assign']]);

    Route::post(':id/capabilities', 'Rbac.Roles/assignCapabilities')
        ->name('roles.assignCapabilities')
        ->setOption('meta', ['name' => '分配能力', 'group' => '角色', 'caps' => ['roles.assign']]);

    Route::patch(':id', 'Rbac.Roles/update')
        ->name('roles.update')
        ->setOption('meta', ['name' => '编辑角色', 'group' => '角色', 'caps' => ['roles.update']]);

    Route::delete(':id', 'Rbac.Roles/delete')
        ->name('roles.delete')
        ->setOption('meta', ['name' => '删除角色', 'group' => '角色', 'caps' => ['roles.delete']]);
})->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
