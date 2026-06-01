<?php

namespace app\api\service\Content;

use think\facade\Db;

use app\api\model\Comments as CommentsModel;
use app\api\model\Cards as CardsModel;

use app\common\support\FieldsToggle;
use app\common\support\ModelList;
use app\common\support\OwnershipGuard;

class Comments
{
    use OwnershipGuard;

    protected static string $guardModel = CommentsModel::class;

    /**
     * 更新评论（用户自己的）
     *
     * @param int   $uid
     * @param array $data
     * @param array $caps
     */
    public static function updateComment(array $data, int $uid, array $caps): void
    {
        Db::startTrans();
        try {
            // 验证能力 + 归属
            self::guard($data['id'], $uid, $caps, 'comments.update');

            // 移除敏感字段
            unset($data['user_id']);

            CommentsModel::update($data, ['id' => $data['id']]);

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            if ($th instanceof \app\api\ApiException) {
                throw $th;
            }
            throw \app\api\ApiException::error('更新失败', \app\api\ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    public static function newList(array $params, int $user_id = -1): array
    {
        $params['search_default_key'] = 'content';
        if ($user_id != -1) {
            $params['where'] = [
                'status' => [0, 1, 3],
                'user_id' => $user_id
            ];
        }
        $result = ModelList::make(CommentsModel::class)->getPaginate($params);
        return $result->toArray();
    }

    /**
     * @param array $params
     * @param array $caps
     */
    public static function listAll(array $params, array $caps = []): array
    {
        $params['search_default_key'] = 'content';

        // 无 comments.read.all 能力 → 只看已发布
        if (!in_array('comments.read.all', $caps)) {
            $params['where']['status'] = 0;
        }

        $result = ModelList::make(CommentsModel::class)->getPaginate($params);
        return $result->toArray();
    }

    public static function get(int $id): array
    {
        $result = CommentsModel::where('id', $id)->findOrEmpty();
        if ($result->isEmpty()) {
            throw \app\api\ApiException::notFound('评论不存在');
        }
        return $result->toArray();
    }

    public static function createComment($params): array
    {
        $id = $params['id'];
        unset($params['id']);
        $params['aid'] = 1;
        $params['pid'] = $id;

        Db::startTrans();
        try {
            $comment = CommentsModel::create($params);
            CardsModel::where('id', $id)->where('status', 0)->inc('comments')->update();

            Db::commit();
            return ['data' => $comment];
        } catch (\Throwable $th) {
            Db::rollback();
            throw \app\api\ApiException::error('创建失败', \app\api\ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    /**
     * 删除评论
     *
     * @param array $ids
     * @param int   $uid
     * @param array $caps
     */
    public static function deleteComments(array $ids, int $uid, array $caps): void
    {
        if (empty($ids)) {
            throw \app\api\ApiException::badRequest('未指定要删除的评论');
        }

        // 验证能力 + 归属
        self::guardBatch($ids, $uid, $caps, 'comments.delete');

        CommentsModel::destroy($ids);
    }

    /**
     * 批量操作
     *
     * @param string $method
     * @param array  $ids
     * @param int    $uid
     * @param array  $caps
     */
    public static function batchOperate(string $method, array $ids, int $uid, array $caps): void
    {
        if (empty($ids)) {
            throw \app\api\ApiException::badRequest('未指定要操作的资源');
        }

        $opCaps = [
            'approve' => 'comments.update',
            'ban'     => 'comments.update',
            'hide'    => 'comments.update',
            'delete'  => 'comments.delete',
        ];

        $cap = $opCaps[$method] ?? null;
        if (!$cap) {
            throw \app\api\ApiException::badRequest('不支持的操作');
        }

        self::guardBatch($ids, $uid, $caps, $cap);

        match ($method) {
            'approve' => FieldsToggle::toggle(CommentsModel::class, 'status', $ids, [0, 3], [1, 2]),
            'ban' => FieldsToggle::toggle(CommentsModel::class, 'status', $ids, [0, 1], [2, 3]),
            'hide' => FieldsToggle::toggle(CommentsModel::class, 'status', $ids, [0, 2], [1, 3]),
            'delete' => CommentsModel::destroy($ids),
        };
    }
}
