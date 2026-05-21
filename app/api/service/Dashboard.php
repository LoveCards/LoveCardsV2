<?php

namespace app\api\service;

use think\facade\Db;
use app\common\cache\CacheManager;

use app\api\model\Cards as CardsModel;
use app\api\model\Likes;
use app\api\model\Comments as CommentsModel;

class Dashboard
{
    public static function index(): array
    {
        $notice = self::getNotice();

        $count = [
            'cards' => CardsModel::count(),
            'comments' => CommentsModel::count(),
            'good' => Likes::count()
        ];

        $chart = [
            ['label' => '卡片', 'data' => self::getChartData('cards')],
            ['label' => '评论', 'data' => self::getChartData('comments')],
            ['label' => '点赞', 'data' => self::getChartData('likes')],
        ];

        return [
            'count' => $count,
            'chart' => $chart,
            'ver' => [
                'Name' => 'LoveCards',
                'Url' => '//lovecards.cn',
                'VerS' => '2.4.1',
                'Ver' => '21',
                'GithubUrl' => '//github.com/LoveCards/LoveCardsV2',
                'QGroupUrl' => '//jq.qq.com/?_wv=1027&k=qM8f2RMg',
            ],
            'notice' => $notice,
        ];
    }

    private static function getNotice(): array
    {
        return CacheManager::get('system', 'Notice', function () {
            $url = 'https://server.lovecards.cn/apiv1/Notice';
            $ctx = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 5,
                    'header' => "User-Agent: PHP\r\n",
                ],
            ]);

            $jsonStr = @file_get_contents($url, false, $ctx);
            if ($jsonStr === false) {
                return [];
            }

            $json = json_decode($jsonStr, true);
            if (!isset($json['ec']) || $json['ec'] !== 200 || !isset($json['data'])) {
                return [];
            }

            return $json['data'];
        }, CacheManager::TTL_LONG * 3);
    }

    private static function getChartData(string $table): array
    {
        for ($i = 1; $i <= 6; $i++) {
            $time = date('Y-m-d', strtotime('-' . $i . 'day'));
            $arr['y'][$i] = Db::table($table)->whereDay('created_at', $time)->count();
            $arr['x'][$i] = $time;
            if ($i == 1) $arr['x'][$i] = '昨日';
        }
        $arr['y'] = array_reverse($arr['y']);
        $arr['x'] = array_reverse($arr['x']);
        return $arr;
    }
}
