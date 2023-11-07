<?php

namespace jwt;

use Firebase\JWT\JWT as FBJWT;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use DomainException;
use InvalidArgumentException;
use UnexpectedValueException;

use think\Facade;
use think\facade\Config;
use think\facade\Cache;
use app\Common\common;

class Jwt extends Facade
{
    //生成验签
    protected static function SignToken($data): string
    {
        $jwt_config = Config::get('jwt');
        $privateKey = file_get_contents('..' . $jwt_config['privateKey']);

        $payload = array(
            "iss" => $jwt_config['iss'],        //签发者 可以为空
            "aud" => '',          //面象的用户，可以为空
            "iat" => time(),      //签发时间
            "nbf" => time(),    //生效时间
            //"exp" => time() + $jwt_config['exp'], //token 过期时间(改为自定校验)
            "data" => $data           //记录的userid的信息，这里是自已添加上去的，如果有其它信息，可以再添加数组的键值对
        );
        $token = FBJWT::encode($payload, $privateKey, $jwt_config['alg']);
        Cache::set('token_' . $token, time(), $jwt_config['cacheTime']); //设置缓存
        return $token; //根据参数生成了token，可选：HS256、HS384、HS512、RS256、ES256等
    }

    //验证token
    protected static function CheckToken($token): array
    {
        $jwt_config = Config::get('jwt');
        $publicKey = file_get_contents('..' . $jwt_config['publicKey']);

        try {
            //FBJWT::$leeway = 60; //当前时间减去60，把时间留点余地
            //解码
            $decoded = FBJWT::decode($token, new Key($publicKey, $jwt_config['alg']));
            $exp = $decoded->iat + $jwt_config['exp'];
            $data = (array) $decoded->data;
            $data['token'] = null;

            //判断是否过期
            if (time() > $exp) {
                //token过期尝试刷新
                $request = self::RenewToken($token, $data);
                if ($request['status'] == false) {
                    //刷新失败
                    return common::mArrayEasyReturnStruct($request['msg'], false);
                }
                //刷新成功并返回新的token和data
                $data['token'] = $request['data'];
                return common::mArrayEasyReturnStruct(null, true, $data);
            }

            return common::mArrayEasyReturnStruct(null, true, $data);
        } catch (SignatureInvalidException $e) {
            //签名不正确
            return  common::mArrayEasyReturnStruct('签名不正确', false);
        } catch (BeforeValidException $e) {
            // 签名在某个时间点之后才能用
            return common::mArrayEasyReturnStruct('token失效1', false);
        } catch (ExpiredException $e) {
            // token过期 校验payload的exp:data字段
            return common::mArrayEasyReturnStruct('token失效0', false);
        } catch (UnexpectedValueException $e) {
            //其他错误
            return common::mArrayEasyReturnStruct('未知错误:' . $e->getMessage(), false);
        }
    }

    //刷新token
    protected static function RenewToken($token, $data): array
    {
        if (Cache::has('token_' . $token)) {
            //删除原token
            Cache::delete('token_' . $token);
            //更新token
            $token = self::SignToken($data);
            //返回token
            return common::mArrayEasyReturnStruct(null, true, $token);
        }

        return common::mArrayEasyReturnStruct('token失效', false);
    }

    //删除Token
    protected static function DeleteToken($token): array
    {
        if (Cache::has('token_' . $token)) {
            Cache::delete('token_' . $token);
        };
        return common::mArrayEasyReturnStruct(null, true);
    }
}
