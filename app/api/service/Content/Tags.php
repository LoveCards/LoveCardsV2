<?php

namespace app\api\service\Content;

use think\facade\Db;

use app\api\model\Tags as TagsModel;
use app\api\model\TagsMap as TagsMapModel;

use app\common\support\FieldsToggle;

use app\common\support\ModelList;

use app\common\service\Traits\Ownable;

class Tags
{
    use Ownable;
    
    const MODEL_CLASS = TagsModel::class;
    const OWNER_FIELD = 'user_id';
    const RESOURCE_NAME = '标签';
    
    static public function noPaginateIndex($params): array
    {
        $params['search_default_key'] = 'name';
        $result = ModelList::make(TagsModel::class)->getNoPaginate($params);
        return $result->toArray();
    }

    static public function get(int $id): array
    {
        $result = TagsModel::where('id', $id)->findOrEmpty();
        if ($result->isEmpty()) {
            throw \app\api\ApiException::notFound('标签不存在', \app\api\ApiException::CODE_RESOURCE_NOT_FOUND);
        }
        return $result->toArray();
    }

    static public function Index($params): array
    {
        $params['search_default_key'] = 'name';
        $result = ModelList::make(TagsModel::class)->getPaginate($params);
        return $result->toArray();
    }

    static public function listAll($params): array
    {
        return self::Index($params);
    }

    static public function createTag($params): void
    {
        $params['aid'] = 1;
        TagsModel::create($params);
    }

    static public function allCreate($params): void
    {
        self::createTag($params);
    }

    static public function batchOperate($method, $ids): void
    {
        switch ($method) {
            case 'approve':
                FieldsToggle::toggle(TagsModel::class, 'status', $ids, [0, 3], [1, 2]);
                break;
            case 'ban':
                FieldsToggle::toggle(TagsModel::class, 'status', $ids, [0, 1], [2, 3]);
                break;
            case 'hide':
                FieldsToggle::toggle(TagsModel::class, 'status', $ids, [0, 2], [1, 3]);
                break;
            case 'delete':
                self::deleteTags($ids);
                break;
            default:
                throw \app\api\ApiException::badRequest('方法不存在', \app\api\ApiException::CODE_PARAM_INVALID);
        }
    }

    /**
     * 删除标签
     * 
     * @param array $ids 资源 ID 列表
     * @param int|null $uid 当前用户 ID（用户操作必传，管理员操作传 null）
     * @return void
     */
    static public function deleteTags(array $ids, ?int $uid = null): void
    {
        if (empty($ids)) {
            throw \app\api\ApiException::badRequest('未指定要删除的标签');
        }
        
        // 验证所有权
        self::assertOwnerBatchIf($ids, $uid);
        
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

    static public function deleteTagsMap(array $ids): void
    {
        TagsMapModel::where('tag_id', 'in', $ids)->delete();
    }

    /**
     * 更新标签
     * 
     * @param array $data 标签数据
     * @param int|null $uid 当前用户 ID（用户操作必传，管理员操作传 null）
     * @return int 更新的记录数
     */
    static public function updateTag(array $data, ?int $uid = null): void
    {
        // 验证所有权
        self::assertOwnerIf($data['id'], $uid);
        
        TagsModel::update($data);
    }

}
