<?php

namespace app\system\utils;

//use app\system\utils\Common;

class Rsa
{

    public static $rsaPath = '../config/rsa/';

    private static function overwriteFile($filePath, $content)
    {
        // 使用 file_put_contents 覆盖文件内容
        return file_put_contents($filePath, $content);
    }

    /**
     * 生成公私钥对
     *
     * @return array|bool
     */
    public static function Generate()
    {
        try {
            //创建公私钥
            $res = openssl_pkey_new();
            //获取私钥
            openssl_pkey_export($res, $private_key);
            //获取公钥
            $public_key = openssl_pkey_get_details($res)['key'];
            //组合rsa
            $rsa = [
                'public' => $public_key,
                'private' => $private_key,
            ];

            return $rsa;
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * 更新密钥对
     *
     * @param string $public
     * @param string $private
     * @return boolean
     */
    public static function UpdataRsa($public, $private): bool
    {
        $publicPath = self::$rsaPath . 'public.pem';
        $privatePath = self::$rsaPath . 'private.pem';

        try {
            self::overwriteFile($publicPath, $public);
            self::overwriteFile($privatePath, $private);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
