<?php

namespace app\api\service\Content;

use think\facade\Db;

use app\api\model\Cards as CardsModel;
use app\api\model\TagsMap as TagsMapModel;
use app\api\model\Comments as CommentsModel;

use app\common\support\FieldsToggle;
use app\common\support\ModelList;
use app\common\support\OwnershipGuard;

class Cards
{
    use OwnershipGuard;

    protected static string $guardModel = CardsModel::class;

    const TOP_LISTS_MAX = 32;
    const HOT_LISTS_MAX = 8;

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

    /**
     * @param array $params
     * @param array $caps 用户能力列表（公开路由传空数组）
     */
    public static function list(array $params, array $caps = []): array
    {
        $params['search_default_key'] = 'content';

        // 无 cards.read.all 能力 → 只看已发布
        if (!in_array('cards.read.all', $caps)) {
            $params['where']['status'] = 0;
        }

        $result = ModelList::make(CardsModel::class)->getPaginate($params);
        return $result->toArray();
    }

    /**
     * @param int   $id
     * @param array $caps
     */
    public static function get(int $id, array $caps = []): array
    {
        $result = CardsModel::where('id', $id)->findOrEmpty();
        if ($result->isEmpty()) {
            throw \app\api\ApiException::notFound('卡片不存在');
        }

        // 无 cards.read.all 能力 → 只看已发布
        if (!in_array('cards.read.all', $caps) && $result->status !== 0) {
            throw \app\api\ApiException::notFound('卡片不存在');
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

    /**
     * 批量操作（能力在 Service 层检查）
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
            'top'       => 'cards.pin',
            'unset_top' => 'cards.pin',
            'approve'   => 'cards.approve',
            'ban'       => 'cards.approve',
            'hide'      => 'cards.update',
            'unhide'    => 'cards.update',
            'delete'    => 'cards.delete',
        ];

        $cap = $opCaps[$method] ?? null;
        if (!$cap) {
            throw \app\api\ApiException::badRequest('不支持的操作');
        }

        // 能力 + 归属一体化检查
        self::guardBatch($ids, $uid, $caps, $cap);

        match ($method) {
            'top', 'unset_top' => FieldsToggle::toggle(CardsModel::class, 'is_top', $ids, [0, 1]),
            'approve' => FieldsToggle::toggle(CardsModel::class, 'status', $ids, [0, 3], [1, 2]),
            'ban' => FieldsToggle::toggle(CardsModel::class, 'status', $ids, [0, 1], [2, 3]),
            'hide' => FieldsToggle::toggle(CardsModel::class, 'status', $ids, [0, 2], [1, 3]),
            'delete' => self::deleteCardsWithRelated($ids),
        };
    }

    public static function createCard($data): string
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
     * @param int   $uid  当前用户 ID
     * @param array $caps 用户能力列表
     */
    public static function updateCard(array $data, int $uid, array $caps): void
    {
        Db::startTrans();
        try {
            // 验证能力 + 归属
            self::guard($data['id'], $uid, $caps, 'cards.update');

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
            if ($th instanceof \app\api\ApiException) {
                throw $th;
            }
            throw \app\api\ApiException::error('更新失败', \app\api\ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    /**
     * 更新标签关联
     *
     * @param array $data 包含 id 和 tags 的数据
     * @param bool  $create 是否为创建模式（不删除旧关联）
     */
    public static function updateCardTags(array $data, bool $create = false): void
    {
        $pid = (int) $data['id'];
        $tags = is_string($data['tags']) ? json_decode($data['tags'], true) : $data['tags'];

        if (!is_array($tags)) {
            throw \app\api\ApiException::badRequest('标签数据格式错误');
        }

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
     * @param int   $uid 当前用户 ID
     * @param array $caps 用户能力列表
     */
    public static function deleteCards(array $ids, int $uid, array $caps): void
    {
        if (empty($ids)) {
            throw \app\api\ApiException::badRequest('未指定要删除的卡片');
        }

        Db::startTrans();
        try {
            // 验证能力 + 归属
            self::guardBatch($ids, $uid, $caps, 'cards.delete');

            self::deleteCardsTags($ids);
            self::deleteCardsComments($ids);

            CardsModel::destroy($ids);

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            if ($th instanceof \app\api\ApiException) {
                throw $th;
            }
            throw \app\api\ApiException::error('删除失败', \app\api\ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
    }

    public static function deleteCardsTags($pids): void
    {
        TagsMapModel::where('aid', 1)->where('pid', 'in', $pids)->delete();
    }

    public static function deleteCardsComments($pids): void
    {
        CommentsModel::where('aid', 1)->where('pid', 'in', $pids)->delete();
    }

    /**
     * 删除卡片及其关联数据
     */
    private static function deleteCardsWithRelated(array $ids): void
    {
        Db::startTrans();
        try {
            self::deleteCardsTags($ids);
            self::deleteCardsComments($ids);
            CardsModel::destroy($ids);
            Db::commit();
        } catch (\Throwable $th) {
            Db::rollback();
            throw \app\api\ApiException::error('删除失败', \app\api\ApiException::CODE_SYSTEM_ERROR, null, $th);
        }
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
