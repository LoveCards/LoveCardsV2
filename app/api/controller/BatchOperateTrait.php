<?php

namespace app\api\controller;

use app\api\ApiException;
use app\api\ApiResponse;

/**
 * 批量操作 Trait
 * 子类只需实现 getBatchService() 返回 Service 类名
 */
trait BatchOperateTrait
{
    abstract protected function getBatchService(): string;

    public function batch()
    {
        $params = $this->param(
            \yunarch\validate\Common::class,
            \yunarch\validate\Common::$all_scene['BatchOperate'],
            request()->param()
        );

        $serviceClass = $this->getBatchService();
        $service = new $serviceClass();

        if (!method_exists($service, 'batchOperate')) {
            throw ApiException::error('批量操作未实现');
        }

        $result = $service->batchOperate($params['method'], $params['ids']);
        return ApiResponse::createOk('操作成功', $result);
    }
}
