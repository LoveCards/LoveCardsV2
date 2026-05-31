<?php

namespace app\api\service\Content;

use think\facade\Db;
use app\api\model\Likes as LikesModel;
use app\api\model\Cards as CardsModel;
use app\api\ApiException;

class Likes
{
    const TYPES = ['card', 'comment'];

    public static function like(string $refType, int $refId, int $uid, string $ip): int
    {
        if (!in_array($refType, self::TYPES)) {
            throw ApiException::badRequest('不支持的内容类型', ApiException::CODE_PARAM_INVALID);
        }

        Db::startTrans();
        try {
            $exists = LikesModel::where('pid', $refId)
                ->where('uid', $uid)
                ->find();

            if ($exists) {
                throw ApiException::badRequest('请勿重复点赞');
            }

            LikesModel::create([
                'aid' => 1,
                'pid' => $refId,
                'uid' => $uid,
                'ip' => $ip,
            ]);

            self::incCounter($refId);

            Db::commit();

            return self::count($refId);
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
    }

    public static function unlike(string $refType, int $refId, int $uid): void
    {
        Db::startTrans();
        try {
            $like = LikesModel::where('pid', $refId)
                ->where('uid', $uid)
                ->find();

            if (!$like) {
                throw ApiException::notFound('点赞记录不存在', ApiException::CODE_RESOURCE_NOT_FOUND);
            }

            $like->delete();

            self::decCounter($refId);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
    }

    public static function getUserLikes(int $uid): array
    {
        return LikesModel::where('uid', $uid)->select()->toArray();
    }

    public static function count(int $refId): int
    {
        return (int) LikesModel::where('pid', $refId)->count();
    }

    private static function incCounter(int $refId): void
    {
        CardsModel::where('id', $refId)->inc('goods')->update();
    }

    private static function decCounter(int $refId): void
    {
        CardsModel::where('id', $refId)->where('goods', '>', 0)->dec('goods')->update();
    }
}
