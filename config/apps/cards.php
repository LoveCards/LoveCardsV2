<?php
return [
    'approve'         => ['type' => 'bool', 'default' => false, 'description' => '卡片发布审核'],
    'picture_limit'   => ['type' => 'int',  'default' => 15,    'description' => '图片数量限制'],
    'tag_limit'       => ['type' => 'int',  'default' => 3,     'description' => '标签数量限制'],
    'image_size'      => ['type' => 'int',  'default' => 3,     'description' => '图片大小限制(MB)'],
    'comments_status' => ['type' => 'bool', 'default' => true,  'description' => '评论状态'],
];
