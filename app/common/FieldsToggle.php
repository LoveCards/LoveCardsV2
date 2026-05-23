<?php

namespace app\common;

use think\facade\Db;

class FieldsToggle
{
    /**
     * 通用字段翻转：CASE WHEN 切换
     *
     * @param string $modelClass  TP6 Model 子类（如 CardsModel::class）
     * @param string $field       目标字段名（如 'status', 'is_top'）
     * @param array  $ids         目标记录 ID 列表
     * @param array  $value1      互换的两个值 [a, b]
     * @param mixed  $value2      额外固定切换值数组（可选，如 [1,2] → 都映射为 value1[1]）
     */
    public static function toggle(
        string $modelClass,
        string $field,
        array $ids,
        array $value1 = [0, 1],
        $value2 = false
    ): void {
        $case = "WHEN {$field} = {$value1[0]} THEN {$value1[1]} WHEN {$field} = {$value1[1]} THEN {$value1[0]} ";
        if ($value2) {
            foreach ($value2 as $item) {
                $case .= "WHEN {$field} = {$item} THEN {$value1[1]} ";
            }
        }
        $sql = "CASE {$case}END";
        $modelClass::where('id', 'in', $ids)->update([$field => Db::raw($sql)]);
    }
}
