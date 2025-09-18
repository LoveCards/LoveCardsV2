<?php

namespace app\api\service;

use think\facade\Request;

use app\common\Common;

use app\api\model\Images as ImagesModel;

class Images
{

    /**
     * 读取用户卡片
     *
     * @return void
     */
    static public function CardIndex($params)
    {
        $result = ImagesModel::where('aid', 1)->where('pid', $params['pid'])->select();
        $result = $result->toArray();
        function completeUrls(&$array) //Ai临时待完善
        {
            // 遍历数组
            foreach ($array as &$item) {
                // 检查是否存在 'url' 字段
                if (isset($item['url'])) {
                    // 检查 URL 是否以 http:// 或 https:// 开头
                    if (preg_match('/^https?:\/\//', $item['url'])) {
                        // 如果是完整的 URL，不进行修改
                        continue;
                    } else {
                        // 如果不是完整的 URL，补全为 http://
                        $host = Request::host();
                        $url = $item['url'];

                        // 确保 host 和 url 之间没有多余的 /
                        if (substr($host, -1) === '/' && substr($url, 0, 1) === '/') {
                            $item['url'] = '//' . $host . substr($url, 1);
                        } elseif (substr($host, -1) !== '/' && substr($url, 0, 1) !== '/') {
                            $item['url'] = '//' . $host . '/' . $url;
                        } else {
                            $item['url'] = '//' . $host . $url;
                        }
                    }
                }
            }
            // 重置引用
            unset($item);
        }
        completeUrls($result);
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result);
        }
        return Common::mArrayEasyReturnStruct('查询失败', false);
    }
}
