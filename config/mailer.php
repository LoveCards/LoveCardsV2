<?php

/**
 * 配置文件
 */
return [
    'scheme'          => 'smtp',
    'host'            => '', // 服务器地址
    'username'        => '',
    'password'        => '', // 密码
    'port'            => 465, // SMTP服务器端口号,一般为25
    'options'         => [],
    'dsn'             => '',
    'debug'           => false, // 开启debug模式会直接抛出异常, 记录邮件发送日志
    'left_delimiter'  => '{', // 模板变量替换左定界符, 可选, 默认为 {
    'right_delimiter' => '}', // 模板变量替换右定界符, 可选, 默认为 }
    'log_drive'       => '', // 日志驱动类, 可选, 如果启用必须实现静态 public static function write($content, $level = 'debug') 方法
    'log_path'        => '', // 日志路径, 可选, 不配置日志驱动时启用默认日志驱动, 默认路径是 /path/to/tp-mailer/log, 要保证该目录有可写权限, 最好配置自己的日志路径
    'embed'           => 'cid:', // 邮件中嵌入图片元数据标记
];
