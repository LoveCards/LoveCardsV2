<?php
return [
    'url'             => ['type' => 'string', 'default' => '',         'description' => '站点URL'],
    'name'            => ['type' => 'string', 'default' => 'LoveCards','description' => '站点名称'],
    'title'           => ['type' => 'string', 'default' => 'LoveCards','description' => '站点标题'],
    'icp_id'          => ['type' => 'string', 'default' => '',         'description' => 'ICP备案号'],
    'keywords'        => ['type' => 'string', 'default' => '',         'description' => 'SEO关键词'],
    'description'     => ['type' => 'string', 'default' => '',         'description' => 'SEO描述'],
    'copyright'       => ['type' => 'string', 'default' => '',         'description' => '版权信息'],
    'footer'          => ['type' => 'string', 'default' => '',         'description' => '页脚信息'],
    'theme_directory' => ['type' => 'string', 'default' => 'index',    'description' => '主题目录'],
    'visitor_mode'    => ['type' => 'bool',   'default' => true,       'description' => '访客模式'],
];
