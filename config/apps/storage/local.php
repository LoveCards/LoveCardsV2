<?php
return [
    'root' => 'public/storage',
    'url_prefix' => '/storage',
    'allow_mime_types' => 'image/jpeg,image/png,image/gif,image/webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'max_file_size' => 10485760,
    'path_template' => 'storage/{date}/{uuid}.{ext}',
];
