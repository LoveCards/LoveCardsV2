<?php

namespace app\common\support;

use app\api\ApiException;

/**
 * 资源归属守卫 Trait
 *
 * 在 Service 类中使用：
 *
 * class CardsService
 * {
 *     use OwnershipGuard;
 *     protected static string $guardModel = CardsModel::class;
 * }
 *
 * 设计文档：BackEnd/.dev/docs/RBAC_V2_DESIGN.md §六
 */
trait OwnershipGuard
{
    /**
     * 验证能力 + 归属（单个资源）
     *
     * 逻辑：
     * 1. baseCap.all 在 caps 中 → 放行（跳过归属）
     * 2. baseCap 在 caps 中 → 检查归属（user_id == uid）
     * 3. 都没有 → 403
     *
     * @param int    $id      资源 ID
     * @param int    $uid     当前用户 ID
     * @param array  $caps    用户能力列表
     * @param string $baseCap 基础能力名（如 'cards.update'）
     * @throws ApiException 资源不存在或无权访问
     */
    protected static function guard(int $id, int $uid, array $caps, string $baseCap): void
    {
        $allCap = $baseCap . '.all';

        // 有 .all 能力 → 只检查存在性，跳过归属
        if (in_array($allCap, $caps)) {
            $modelClass = static::$guardModel;
            $resource = $modelClass::where('id', $id)->findOrEmpty();
            if ($resource->isEmpty()) {
                throw ApiException::notFound('资源不存在');
            }
            return;
        }

        // 有基础能力 → 检查存在性 + 归属
        if (in_array($baseCap, $caps)) {
            $modelClass = static::$guardModel;
            $field = static::$guardField ?? 'user_id';

            $resource = $modelClass::where('id', $id)->findOrEmpty();
            if ($resource->isEmpty()) {
                throw ApiException::notFound('资源不存在');
            }

            if ((int) $resource->$field !== $uid) {
                throw ApiException::forbidden('无权操作此资源');
            }

            return;
        }

        // 都没有 → 403
        throw ApiException::forbidden('权限不足');
    }

    /**
     * 验证能力 + 归属（批量资源）
     *
     * @param array  $ids     资源 ID 列表
     * @param int    $uid     当前用户 ID
     * @param array  $caps    用户能力列表
     * @param string $baseCap 基础能力名（如 'cards.delete'）
     * @throws ApiException 部分资源无权访问
     */
    protected static function guardBatch(array $ids, int $uid, array $caps, string $baseCap): void
    {
        if (empty($ids)) {
            return;
        }

        $allCap = $baseCap . '.all';

        // 有 .all 能力 → 跳过归属检查
        if (in_array($allCap, $caps)) {
            return;
        }

        // 有基础能力 → 逐条检查归属
        if (in_array($baseCap, $caps)) {
            $modelClass = static::$guardModel;
            $field = static::$guardField ?? 'user_id';

            // 验证所有 ID 存在
            $existing = $modelClass::whereIn('id', $ids)->column('id');
            $missing = array_diff($ids, $existing);
            if (!empty($missing)) {
                throw ApiException::notFound('资源不存在');
            }

            $notOwned = $modelClass::whereIn('id', $ids)
                ->where($field, '<>', $uid)
                ->column('id');

            if (!empty($notOwned)) {
                throw ApiException::forbidden('无权操作部分资源');
            }

            return;
        }

        // 都没有 → 403
        throw ApiException::forbidden('权限不足');
    }
}
