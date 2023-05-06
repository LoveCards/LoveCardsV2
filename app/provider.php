<?php
use app\ExceptionHandle;

// 容器Provider定义文件 接管报错
return [
    'think\exception\Handle'    =>  ExceptionHandle::class,
];
