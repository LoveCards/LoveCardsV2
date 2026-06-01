<?php

namespace app\api\service\Content;

use think\facade\Db;

use app\api\model\Tags as TagsModel;
use app\api\model\TagsMap as TagsMapModel;

use app\common\support\FieldsToggle;
use app\common\support\ModelList;
use app\common\support\OwnershipGuard;

class Tags
{
    use OwnershipGuard;

    protected static string $guardModel = TagsModel::class;

    public static function noPaginateIndex($params): array
    {
        $params['search_default_key'] = 'name';
        $result = ModelList::make(TagsModel::class)->getNoPaginate($params);
        return $result->toArray();
    }

    public static function get(int $id): array
    {
        $result = TagsModel::where('id', $id)->findOrEmpty();
        if ($result->isEmpty()) {
            throw \app\api\ApiException::notFound('标签不存在');
        }
        return $result->toArray();
    }

    public static function Index($params): array
    {
        $params['search_default_key'] = 'name';
        $result = ModelList::make(TagsModel::class)->getPaginate($params);
        return $result->toArray();
    }

    public static function listAll($params): array
    {
        return self::Index($params);
    }

    public static function createTag($params): void
    {
        $params['aid'] = 1;
        TagsModel::create($params);
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
            'approve' => 'tags.update',
            'ban'     => 'tags.update',
            'hide'    => 'tags.update',
            'delete'  => 'tags.delete',
        ];

        $cap = $opCaps[$method] ?? null;
        if (!$cap) {
            throw \app\api\ApiException::badRequest('不支持的操作');
        }

        self::guardBatch($ids, $uid, $caps, $cap);

        match ($method) {
            'approve' => FieldsToggle::toggle(TagsModel::class, 'status', $ids, [0, 3], [1, 2]),
            'ban' => FieldsToggle::toggle(TagsModel::class, 'status', $ids, [0, 1], [2, 3]),
            'hide' => FieldsToggle::toggle(TagsModel::class, 'status', $ids, [0, 2], [1, 3]),
            'delete' => self::deleteTagsWithMap($ids),
        };
    }

    /**
     * 删除标签
     *
     * @param array $ids
     * @param int   $uid
     * @param array $caps
     */
    public static function deleteTags(array $ids, int $uid, array $caps): void
    {
        if (empty($ids)) {
            throw \app\api\ApiException::badRequest('未指定要删除的标签');
        }

        // 验证能力 + 归属
        self::guardBatch($ids, $uid, $caps, 'tags.delete');

        Db::startTrans();
        try {
            self::deleteTagsMap($ids);
            TagsModel::destroy($ids);
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            throw \app\api\ApiException::error('删除失败', \app\api\ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    public static function deleteTagsMap(array $ids): void
    {
        TagsMapModel::where('tag_id', 'in', $ids)->delete();
    }

    /**
     * 删除标签及其关联映射
     */
    private static function deleteTagsWithMap(array $ids): void
    {
        Db::startTrans();
        try {
            self::deleteTagsMap($ids);
            TagsModel::destroy($ids);
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            throw \app\api\ApiException::error('删除失败', \app\api\ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    /**
     * 更新标签
     *
     * @param array $data
     * @param int   $uid
     * @param array $caps
     */
    public static function updateTag(array $data, int $uid, array $caps): void
    {
        Db::startTrans();
        try {
            // 验证能力 + 归属
            self::guard($data['id'], $uid, $caps, 'tags.update');

            // 移除敏感字段
            unset($data['user_id']);

            TagsModel::update($data);

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            if ($th instanceof \app\api\ApiException) {
                throw $th;
            }
            throw \app\api\ApiException::error('更新失败', \app\api\ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }
}
