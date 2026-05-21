<?php
return [
    'name'               => ['type' => 'string', 'default' => 'LoveCards',                                 'description' => '系统名称'],
    'url'                => ['type' => 'string', 'default' => '//lovecards.cn',                             'description' => '官网地址'],
    'vers'               => ['type' => 'string', 'default' => '2.4.1',                                      'description' => '版本号'],
    'ver'                => ['type' => 'string', 'default' => '21',                                         'description' => '版本序号'],
    'github_url'         => ['type' => 'string', 'default' => '//github.com/LoveCards/LoveCardsV2',         'description' => 'GitHub地址'],
    'qgroup_url'         => ['type' => 'string', 'default' => '//jq.qq.com/?_wv=1027&k=qM8f2RMg',          'description' => 'QQ群地址'],
    'install_environment'=> ['type' => 'json',   'default' => '{"php":{"[":"7.2.5",")":"8.0.99"},"mysql":{"[":"5.7",")":"9999"}}', 'description' => '安装环境要求'],
];
