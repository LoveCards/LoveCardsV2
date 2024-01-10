<?php

namespace app\system\utils;

//use app\system\utils\Common;

class Rsa
{

    /**
     * 生成公私钥对
     *
     * @return array
     */
    public static function Generate(): array
    {
        //创建公私钥
        $res = openssl_pkey_new();

        //获取私钥
        openssl_pkey_export($res, $private_key);

        //获取公钥
        $public_key = openssl_pkey_get_details($res)['key'];

        //组合rsa
        $rsa = [
            'public_key' => $public_key,
            'private_key' => $private_key,
        ];

        return $rsa;
    }
}
