<?php
use think\facade\Route;

// Theme engine catch-all — 必须最后注册，确保 API 路由优先
// 这些路由会在所有应用路由之后执行
Route::get("/", "frontend.Theme/index");
Route::get("/:path", "frontend.Theme/index")->pattern(["path" => "[\\w\\-/]+"]);
Route::rule("", "frontend.Theme/index");
