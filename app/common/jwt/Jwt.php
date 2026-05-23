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
use app\common\cache\CacheManager;

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
            "exp" => time() + $jwt_config['exp'],
            "data" => $data
        ];
        $token = FBJWT::encode($payload, $privateKey, $jwt_config['alg']);
        CacheManager::set('jwt', 'token_' . $token, time(), $jwt_config['cacheTime']);
        return $token;
    }

    public static function verify(string $token): array
    {
        $jwt_config = Config::get('jwt');
        $publicKey = file_get_contents(app()->getRootPath() . $jwt_config['publicKey']);

        try {
            $decoded = FBJWT::decode($token, new Key($publicKey, $jwt_config['alg']), [$jwt_config['alg']]);
            $data = (array) $decoded->data;
            return $data;
        } catch (ExpiredException $e) {
            $newToken = self::_renew($token);
            if ($newToken === null) {
                throw new \RuntimeException('token已失效');
            }
            return ['_new_token' => $newToken];
        } catch (SignatureInvalidException $e) {
            throw new \RuntimeException('签名不正确');
        } catch (BeforeValidException $e) {
            throw new \RuntimeException('token未生效');
        } catch (UnexpectedValueException $e) {
            throw new \RuntimeException('未知错误:' . $e->getMessage());
        }
    }

    private static function _renew(string $token): ?string
    {
        $cacheKey = 'token_' . $token;
        if (Cache::tag('jwt')->get($cacheKey) !== null) {
            Cache::tag('jwt')->delete($cacheKey);
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
        Cache::tag('jwt')->delete('token_' . $token);
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
