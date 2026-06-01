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
            \app\common\validate\Common::class,
            \app\common\validate\Common::$all_scene['BatchOperate'],
            request()->param()
        );

        $serviceClass = $this->getBatchService();
        $service = new $serviceClass();

        if (!method_exists($service, 'batchOperate')) {
            throw ApiException::error('批量操作未实现');
        }

        $service->batchOperate($params['method'], $params['ids'], request()->uid ?? 0, request()->caps ?? []);
        return ApiResponse::createNoContent();
    }
}
