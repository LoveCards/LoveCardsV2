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
            $exists = LikesModel::where('ref_type', $refType)
                ->where('ref_id', $refId)
                ->where('uid', $uid)
                ->find();

            if ($exists) {
                throw ApiException::badRequest('请勿重复点赞');
            }

            LikesModel::create([
                'ref_type' => $refType,
                'ref_id' => $refId,
                'uid' => $uid,
                'ip' => $ip,
            ]);

            self::incCounter($refType, $refId);

            Db::commit();

            return self::count($refType, $refId);
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
    }

    public static function unlike(string $refType, int $refId, int $uid): void
    {
        Db::startTrans();
        try {
            $like = LikesModel::where('ref_type', $refType)
                ->where('ref_id', $refId)
                ->where('uid', $uid)
                ->find();

            if (!$like) {
                throw ApiException::notFound('点赞记录不存在', ApiException::CODE_RESOURCE_NOT_FOUND);
            }

            $like->delete();

            self::decCounter($refType, $refId);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }
    }

    public static function getUserLikes(int $uid, ?string $refType = null): array
    {
        $query = LikesModel::where('uid', $uid);
        if ($refType !== null) {
            $query->where('ref_type', $refType);
        }
        return $query->select()->toArray();
    }

    public static function count(string $refType, int $refId): int
    {
        return (int) LikesModel::where('ref_type', $refType)
            ->where('ref_id', $refId)
            ->count();
    }

    private static function incCounter(string $refType, int $refId): void
    {
        match ($refType) {
            'card' => CardsModel::where('id', $refId)->inc('goods')->update(),
            default => null,
        };
    }

    private static function decCounter(string $refType, int $refId): void
    {
        match ($refType) {
            'card' => CardsModel::where('id', $refId)->where('goods', '>', 0)->dec('goods')->update(),
            default => null,
        };
    }
}
