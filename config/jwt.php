<?php
return [
    'privateKey' => '/config/rsa/private.pem',
    'publicKey' => '/config/rsa/public.pem',
    'alg' => 'RS256',
    'exp' => 3600 * 24, // token过期时间，单位秒
    'iss' => 'http://serverhub.com',
    'cacheTime' => 3600 * 24 * 3
];
