<?php

namespace app\common\service\Traits;

use app\api\ApiException;

/**
 * 资源所有权验证 Trait
 * 
 * 在 Service 类中使用：
 * 
 * class Cards
 * {
 *     use Ownable;
 *     
 *     // 必须声明以下常量
 *     const MODEL_CLASS = CardsModel::class;
 *     const OWNER_FIELD = 'user_id';
 *     const RESOURCE_NAME = '卡片';
 * }
 */
trait Ownable
{
    /**
     * 验证单个资源所有权
     * 
     * @param int $id 资源 ID
     * @param int $uid 当前用户 ID
     * @throws ApiException 资源不存在或无权访问
     */
    protected static function assertOwner(int $id, int $uid): void
    {
        $modelClass = static::MODEL_CLASS;
        $ownerField = static::OWNER_FIELD ?? 'user_id';
        $resourceName = static::RESOURCE_NAME ?? '资源';
        
        $resource = $modelClass::where('id', $id)->findOrEmpty();
        
        if ($resource->isEmpty()) {
            throw ApiException::notFound($resourceName . '不存在');
        }
        
        if ($resource->$ownerField != $uid) {
            throw ApiException::forbidden('无权操作此' . $resourceName);
        }
    }
    
    /**
     * 验证批量资源所有权
     * 
     * @param array $ids 资源 ID 列表
     * @param int $uid 当前用户 ID
     * @throws ApiException 部分资源无权访问
     */
    protected static function assertOwnerBatch(array $ids, int $uid): void
    {
        if (empty($ids)) {
            return;
        }
        
        $modelClass = static::MODEL_CLASS;
        $ownerField = static::OWNER_FIELD ?? 'user_id';
        $resourceName = static::RESOURCE_NAME ?? '资源';
        
        $notOwnedIds = $modelClass::whereIn('id', $ids)
            ->where($ownerField, '<>', $uid)
            ->column('id');
        
        if ($notOwnedIds) {
            $count = count($notOwnedIds);
            if ($count === 1) {
                throw ApiException::forbidden('无权操作此' . $resourceName);
            } else {
                throw ApiException::forbidden('无权操作部分' . $resourceName);
            }
        }
    }
    
    /**
     * 验证资源所有权（管理员跳过）
     * 
     * @param int $id 资源 ID
     * @param int|null $uid 当前用户 ID（null 表示管理员操作）
     * @throws ApiException 资源不存在或无权访问
     */
    protected static function assertOwnerIf(int $id, ?int $uid): void
    {
        if ($uid !== null) {
            self::assertOwner($id, $uid);
        }
    }
    
    /**
     * 验证批量资源所有权（管理员跳过）
     * 
     * @param array $ids 资源 ID 列表
     * @param int|null $uid 当前用户 ID（null 表示管理员操作）
     * @throws ApiException 部分资源无权访问
     */
    protected static function assertOwnerBatchIf(array $ids, ?int $uid): void
    {
        if ($uid !== null) {
            self::assertOwnerBatch($ids, $uid);
        }
    }
}
