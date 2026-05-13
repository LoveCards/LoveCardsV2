<?php

return [
    'local' => [
        'type' => 'local',
        'root' => 'public/storage',
        'url_prefix' => '/storage',
        'allow_mime_types' => 'image/jpeg,image/png,image/gif,image/webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'max_file_size' => 10485760,
    ],

    'oss' => [
        'type' => 'oss',
        'access_key' => env('OSS_ACCESS_KEY', ''),
        'secret_key' => env('OSS_SECRET_KEY', ''),
        'bucket' => env('OSS_BUCKET', ''),
        'endpoint' => env('OSS_ENDPOINT', ''),
        'url_prefix' => env('OSS_URL_PREFIX', ''),
        'allow_mime_types' => 'image/jpeg,image/png,image/gif,image/webp',
        'max_file_size' => 52428800,
    ],

    'cos' => [
        'type' => 'cos',
        'secret_id' => env('COS.COS_SECRET_ID', ''),
        'secret_key' => env('COS.COS_SECRET_KEY', ''),
        'bucket' => env('COS.COS_BUCKET', ''),
        'region' => env('COS.COS_REGION', 'ap-guangzhou'),
        'cdn_url' => env('COS.COS_CDN_URL', ''),
        'allow_mime_types' => 'image/jpeg,image/png,image/gif,image/webp',
        'max_file_size' => 52428800,
    ],

    'qiniu' => [
        'type' => 'qiniu',
        'access_key' => env('QINIU_ACCESS_KEY', ''),
        'secret_key' => env('QINIU_SECRET_KEY', ''),
        'bucket' => env('QINIU_BUCKET', ''),
        'domain' => env('QINIU_DOMAIN', ''),
        'allow_mime_types' => 'image/jpeg,image/png,image/gif,image/webp',
        'max_file_size' => 52428800,
    ],

    'smms' => [
        'type' => 'api',
        'api_key' => env('SMMS_API_KEY', ''),
        'allow_mime_types' => 'image/jpeg,image/png,image/gif,image/webp',
        'max_file_size' => 10485760,
    ],
];