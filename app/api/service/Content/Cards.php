<?php

namespace app\api\service\Content;

use think\facade\Db;

use app\api\model\Cards as CardsModel;
use app\api\model\TagsMap as TagsMapModel;
use app\api\model\Comments as CommentsModel;

use app\common\support\FieldsToggle;

use app\common\support\ModelList;

use app\common\service\Traits\Ownable;

class Cards
{
    use Ownable;
    
    const TOP_LISTS_MAX = 32;
    const HOT_LISTS_MAX = 8;
    
    const MODEL_CLASS = CardsModel::class;
    const OWNER_FIELD = 'user_id';
    const RESOURCE_NAME = '卡片';

    public static function hotList(): array
    {
        $top = CardsModel::where('status', 0)
            ->where('is_top', 1)
            ->order('id', 'desc')
            ->limit(self::TOP_LISTS_MAX)
            ->select()
            ->toArray();

        $hot = CardsModel::fieldRaw('*, comments * 0.3 + goods * 0.7 as hot_score')
            ->where('status', 0)
            ->where('is_top', 0)
            ->order('hot_score', 'desc')
            ->limit(self::HOT_LISTS_MAX)
            ->select()
            ->toArray();

        return array_merge($top, $hot);
    }

    public static function list(array $params, int $user_id = -1): array
    {
        $params['search_default_key'] = 'content';
        if ($user_id != -1) {
            $params['where'] = [
                'status' => [0, 1, 3],
                'user_id' => $user_id
            ];
        }
        $result = ModelList::make(CardsModel::class)->getPaginate($params);

        return $result->toArray();
    }

    public static function get(int $id): array
    {
        $result = CardsModel::where('id', $id)->where('status', 0)->findOrEmpty();
        if ($result->isEmpty()) {
            throw \app\api\ApiException::notFound('卡片不存在', \app\api\ApiException::CODE_RESOURCE_NOT_FOUND);
        }
        return self::decodeJsonFields($result->toArray());
    }

    public static function getAny(int $id): array
    {
        $result = CardsModel::where('id', $id)->findOrEmpty();
        if ($result->isEmpty()) {
            throw \app\api\ApiException::notFound('卡片不存在', \app\api\ApiException::CODE_RESOURCE_NOT_FOUND);
        }
        return self::decodeJsonFields($result->toArray());
    }

    public static function listOwn(array $params, int $uid): array
    {
        $params['search_default_key'] = 'content';
        $params['where'] = [
            'user_id' => $uid
        ];
        $result = ModelList::make(CardsModel::class)->getPaginate($params);

        return $result->toArray();
    }

    public static function listAll(array $params): array
    {
        $params['search_default_key'] = 'content';
        $result = ModelList::make(CardsModel::class)->getPaginate($params);

        return $result->toArray();
    }

    static public function batchOperate($method, $ids): void
    {
        switch ($method) {
            case 'top':
                FieldsToggle::toggle(CardsModel::class, 'is_top', $ids, [0, 1]);
                break;
            case 'approve':
                FieldsToggle::toggle(CardsModel::class, 'status', $ids, [0, 3], [1, 2]);
                break;
            case 'ban':
                FieldsToggle::toggle(CardsModel::class, 'status', $ids, [0, 1], [2, 3]);
                break;
            case 'hide':
                FieldsToggle::toggle(CardsModel::class, 'status', $ids, [0, 2], [1, 3]);
                break;
            case 'delete':
                self::deleteCards($ids);
                break;
            default:
                throw \app\api\ApiException::badRequest('方法不存在', \app\api\ApiException::CODE_PARAM_INVALID);
        }
    }

    static public function createCard($data): string
    {
        Db::startTrans();
        try {
            if (isset($data['data']) && is_array($data['data'])) {
                $data['data'] = json_encode($data['data']);
            }
            if (isset($data['tags']) && is_array($data['tags'])) {
                $data['tags'] = json_encode($data['tags']);
            }
            $result = CardsModel::create($data);
            $data['id'] = $result->id;
            if (isset($data['tags'])) {
                self::updateCardTags($data, true);
            }
            Db::commit();
            return $result->id;
        } catch (\Throwable $th) {
            Db::rollback();
            throw \app\api\ApiException::error('创建失败', \app\api\ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    /**
     * 更新卡片
     * 
     * @param array $data 卡片数据
     * @param int|null $uid 当前用户 ID（用户操作必传，管理员操作传 null）
     * @return void
     */
    static public function updateCard(array $data, ?int $uid = null): void
    {
        Db::startTrans();
        try {
            // 验证所有权
            self::assertOwnerIf($data['id'], $uid);
            
            // 移除敏感字段
            unset($data['user_id']);
            
            if (isset($data['tags'])) {
                self::updateCardTags($data);
            }

            if (isset($data['data']) && is_array($data['data'])) {
                $data['data'] = json_encode($data['data']);
            }
            if (isset($data['tags']) && is_array($data['tags'])) {
                $data['tags'] = json_encode($data['tags']);
            }
            
            CardsModel::update($data);

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            throw \app\api\ApiException::error('更新失败', \app\api\ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    static public function updateCardTags($data, $create = false): void
    {
        $pid = (int) $data['id'];
        $tags = json_decode($data['tags'], true);

        if (!$create) {
            TagsMapModel::where('aid', 1)->where('pid', $pid)->delete();
        }

        foreach ($tags as $tag_id) {
            $item = [
                'aid' => 1,
                'pid' => $pid,
                'tag_id' => $tag_id,
            ];
            TagsMapModel::create($item);
        }
    }

    /**
     * 删除卡片
     * 
     * @param array $ids 资源 ID 列表
     * @param int|null $uid 当前用户 ID（用户操作必传，管理员操作传 null）
     * @return void
     */
    static public function deleteCards(array $ids, ?int $uid = null): void
    {
        if (empty($ids)) {
            throw \app\api\ApiException::badRequest('未指定要删除的卡片');
        }
        
        Db::startTrans();
        try {
            // 验证所有权
            self::assertOwnerBatchIf($ids, $uid);
            
            self::deleteCardsTags($ids);
            self::deleteCardsComments($ids);

            CardsModel::destroy($ids);

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            throw \app\api\ApiException::error('删除失败', \app\api\ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    static public function deleteCardsTags($pids): void
    {
        TagsMapModel::where('aid', 1)->where('pid', 'in', $pids)->delete();
    }

    static public function deleteCardsComments($pids): void
    {
        CommentsModel::where('aid', 1)->where('pid', 'in', $pids)->delete();
    }

    private static function decodeJsonFields(array $card): array
    {
        foreach (['data', 'tags', 'pictures'] as $field) {
            if (isset($card[$field]) && is_string($card[$field])) {
                $decoded = json_decode($card[$field], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $card[$field] = $decoded;
                }
            }
        }
        return $card;
    }

}
