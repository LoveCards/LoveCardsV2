<?php

namespace app\common\extend\jwt;

use Firebase\JWT\JWT as FBJWT;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use DomainException;
use InvalidArgumentException;
use UnexpectedValueException;

use think\facade\Config;
use think\facade\Cache;
use app\api\ApiException;

class Jwt
{
    public static function signToken($data): string
    {
        $jwt_config = Config::get('jwt');
        $privateKey = file_get_contents('..' . $jwt_config['privateKey']);

        $payload = array(
            "iss" => $jwt_config['iss'],
            "aud" => '',
            "iat" => time(),
            "nbf" => time(),
            "data" => $data
        );
        $token = FBJWT::encode($payload, $privateKey, $jwt_config['alg']);
        Cache::set('token_' . $token, time(), $jwt_config['cacheTime']);
        return $token;
    }

    public static function checkToken($token): array
    {
        $jwt_config = Config::get('jwt');
        $publicKey = file_get_contents('..' . $jwt_config['publicKey']);

        try {
            $decoded = FBJWT::decode($token, new Key($publicKey, $jwt_config['alg']));
            $exp = $decoded->iat + $jwt_config['exp'];
            $data = (array) $decoded->data;
            $data['token'] = null;

            if (time() > $exp) {
                $request = self::renewToken($token, $data);
                if (!$request['status']) {
                    throw ApiException::unauthorized($request['msg'], ApiException::CODE_TOKEN_EXPIRED);
                }
                $data['token'] = $request['data'];
            }

            return [
                'status' => true,
                'msg' => null,
                'data' => $data,
            ];
        } catch (SignatureInvalidException $e) {
            throw ApiException::unauthorized('签名不正确', ApiException::CODE_TOKEN_INVALID);
        } catch (BeforeValidException $e) {
            throw ApiException::unauthorized('token未生效', ApiException::CODE_TOKEN_INVALID);
        } catch (ExpiredException $e) {
            throw ApiException::unauthorized('token已失效', ApiException::CODE_TOKEN_EXPIRED);
        } catch (UnexpectedValueException $e) {
            throw ApiException::unauthorized('未知错误:' . $e->getMessage(), ApiException::CODE_UNKNOWN);
        }
    }

    public static function renewToken($token, $data): array
    {
        if (Cache::has('token_' . $token)) {
            Cache::delete('token_' . $token);
            $token = self::signToken($data);
            return [
                'status' => true,
                'msg' => null,
                'data' => $token,
            ];
        }

        return [
            'status' => false,
            'msg' => 'token失效',
            'data' => null,
        ];
    }

    public static function deleteToken($token): void
    {
        if (Cache::has('token_' . $token)) {
            Cache::delete('token_' . $token);
        }
    }

    public static function verifyRsa($publicKey, $privateKey): bool
    {
        $jwt_config = Config::get('jwt');
        $payload = ['Test' => true];

        try {
            $token = FBJWT::encode($payload, $privateKey, $jwt_config['alg']);
            $decoded = FBJWT::decode($token, new Key($publicKey, $jwt_config['alg']));
            if ($decoded->Test == $payload['Test']) {
                return true;
            }
            return false;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
