<?php

namespace app\api\service\Content;

use app\api\model\Models;

/**
 * 字段显隐控制 Trait
 * 子类只需实现 getModel() 返回模型类名
 */
trait FieldsToggleTrait
{
    abstract protected function getModel(): string;

    public function fieldsToggle($data, $statusField = 'status')
    {
        $modelClass = $this->getModel();

        if (is_array($data) && isset($data['list'])) {
            foreach ($data['list'] as &$item) {
                if (isset($item[$statusField]) && $item[$statusField] == 0) {
                    $item['isHide'] = true;
                } else {
                    $item['isHide'] = false;
                }
            }
        }

        return $data;
    }
}
