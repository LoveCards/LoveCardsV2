<?php

namespace app\api\controller\admin;

use think\facade\Db;
use think\facade\Session;

use app\api\model\Cards as CardsModel;
use app\api\model\Likes as LikesModel;
use app\api\model\Comments as CommentsModel;

use app\api\controller\ApiResponse;

use app\common\Common;

class Dashboard
{

    //列表
    public function Index()
    {
        /**
         * 获取公告列表（原生网络请求版）
         * 1. 先读 Session，存在直接返回
         * 2. 不存在则用 file_get_contents 请求接口
         * 3. 成功后写 Session（1 小时过期）
         *
         * @return array 公告列表（data 字段）
         */
        function getNotice(): array
        {
            // 1. 从 Session 取
            $notice = Session::get('Notice');
            if ($notice !== null) {
                return $notice;
            }

            // 2. 原生 GET 请求
            $url  = 'https://server.lovecards.cn/apiv1/Notice';
            $ctx  = stream_context_create([
                'http' => [
                    'method'  => 'GET',
                    'timeout' => 5,          // 5 秒超时
                    'header'  => "User-Agent: PHP\r\n",
                ],
                'ssl'  => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $jsonStr = @file_get_contents($url, false, $ctx);
            if ($jsonStr === false) {
                // 请求失败，返回空数组
                return [];
            }

            // 3. 解析 JSON
            $json = json_decode($jsonStr, true);
            if (!isset($json['ec']) || $json['ec'] !== 200 || !isset($json['data'])) {
                return [];
            }

            // 4. 写入 Session 并返回
            Session::set('Notice', $json['data'], 3600 * 3);
            return $json['data'];
        }
        $notice = getNotice();

        //函数-取图表数据
        function fArrayGetChartData($key)
        {
            for ($i = 1; $i <= 6; $i++) {
                $time = date('Y-m-d', strtotime('-' . $i . 'day'));
                $arr['y'][$i] = Db::table($key)->whereDay('created_at', $time)->count();
                $arr['x'][$i] = $time;
                if ($i == 1) $arr['x'][$i] = '昨日';
            }
            $arr['y'] = Array_reverse($arr['y']);
            $arr['x'] = Array_reverse($arr['x']);
            return $arr;
        }

        //取总览数据
        $tDef_ViewDataCount = [
            'cards' => CardsModel::count(),
            'comments' => CommentsModel::count(),
            'good' => LikesModel::count()
        ];
        //取图表数据
        $tDef_ViewChartJson = [
            [
                'label' => '卡片',
                'data' => fArrayGetChartData('cards'),
            ],
            [
                'label' => '评论',
                'data' => fArrayGetChartData('comments'),
            ],
            [
                'label' => '点赞',
                'data' => fArrayGetChartData('good'),
            ],
        ];

        $result = [
            'count' => $tDef_ViewDataCount,
            'chart' => $tDef_ViewChartJson,
            'ver' => Common::mArrayGetLCVersionInfo(),
            'notice' => $notice,
        ];

        return ApiResponse::createSuccess($result);
    }
}
