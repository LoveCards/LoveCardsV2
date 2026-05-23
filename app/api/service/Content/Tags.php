<?php

namespace app\api\service\Content;

use think\facade\Db;

use app\api\model\Tags as TagsModel;
use app\api\model\TagsMap as TagsMapModel;

use app\common\FieldsToggle;

use yunarch\utils\src\ModelList;

class Tags
{
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
                self::deleteTags(false, $ids);
                break;
            default:
                throw \app\api\ApiException::badRequest('方法不存在', \app\api\ApiException::CODE_PARAM_INVALID);
        }
    }

    static public function deleteTags($id = false, $ids = []): void
    {
        $data = $id ? $id : $ids;
        Db::startTrans();
        try {
            self::deleteTagsMap($data);

            TagsModel::destroy($data);

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

    static public function updateTag(array $data): int
    {
        return TagsModel::update($data);
    }

    static public function updateAny(array $data): int
    {
        return self::updateTag($data);
    }

    static public function deleteAny($id = false, $ids = []): void
    {
        self::deleteTags($id, $ids);
    }
}
