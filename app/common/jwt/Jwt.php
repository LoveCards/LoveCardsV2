<?php

namespace app\common\jwt;

use Firebase\JWT\JWT as FBJWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use UnexpectedValueException;

use think\facade\Config;
use think\facade\Cache;
use app\api\ApiException;

class Jwt
{
    public static function sign(array $data): string
    {
        $jwt_config = Config::get('jwt');
        $privateKey = file_get_contents(app()->getRootPath() . $jwt_config['privateKey']);

        $payload = [
            "iss" => $jwt_config['iss'],
            "iat" => time(),
            "nbf" => time(),
            "data" => $data
        ];
        $token = FBJWT::encode($payload, $privateKey, $jwt_config['alg']);
        Cache::set('token_' . $token, time(), $jwt_config['cacheTime']);
        return $token;
    }

    public static function verify(string $token): array
    {
        $jwt_config = Config::get('jwt');
        $publicKey = file_get_contents(app()->getRootPath() . $jwt_config['publicKey']);

        try {
            $decoded = FBJWT::decode($token, new Key($publicKey, $jwt_config['alg']), [$jwt_config['alg']]);
            $exp = $decoded->iat + $jwt_config['exp'];
            $data = (array) $decoded->data;

            if (time() > $exp) {
                $newToken = self::_renew($token);
                if ($newToken === null) {
                    throw ApiException::unauthorized('token失效', ApiException::CODE_TOKEN_EXPIRED);
                }
                $data['_new_token'] = $newToken;
            }

            return $data;
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

    private static function _renew(string $token): ?string
    {
        if (Cache::has('token_' . $token)) {
            Cache::delete('token_' . $token);
            $jwt_config = Config::get('jwt');
            $privateKey = file_get_contents(app()->getRootPath() . $jwt_config['privateKey']);

            $decoded = FBJWT::decode($token, new Key(file_get_contents(app()->getRootPath() . $jwt_config['publicKey']), $jwt_config['alg']), [$jwt_config['alg']]);
            $data = (array) $decoded->data;

            return self::sign($data);
        }

        return null;
    }

    public static function invalidate(string $token): void
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
            $decoded = FBJWT::decode($token, new Key($publicKey, $jwt_config['alg']), [$jwt_config['alg']]);
            if ($decoded->Test == $payload['Test']) {
                return true;
            }
            return false;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
