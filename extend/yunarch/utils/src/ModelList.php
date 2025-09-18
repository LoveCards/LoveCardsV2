<?php

namespace yunarch\utils\src;

use think\Model;

/**
 * 通用模型超级分页查询类
 * 提供基于模型的灵活分页查询功能，支持条件筛选、字段过滤、排序等操作
 */
class ModelList
{

    /**
     * 查询对象
     * @var Model
     */
    private $model;

    /**
     * 查询对象
     * @var Model
     */
    private $query;

    /**
     * 构造函数
     * @param Model $model 传入的模型对象
     */
    function __construct(Model $model)
    {
        $this->model = $model;
        $this->query = $this->model;

        return $this;
    }

    /**
     * 设置模型
     * @param array $params 全部查询参数
     * @param string $params['search_default_key'] 默认搜索字段
     * @param array $params['where'] 额外查询条件
     * @param array $params['withoutField'] 排除字段
     * @param array $params['search_keys'] 搜索字段
     * @param string|null $params['search_value'] 搜索值
     * @param int $params['search_like'] 搜索模式 0前缀匹配|1后缀匹配|2模糊匹配
     * @param string $params['order_key'] 排序字段
     * @param bool $params['order_desc'] 排序方式，true为降序，false为升序
     * @param int $params['page'] 页码
     * @param int $params['list_rows'] 每页记录数
     * @return object 返回当前对象，支持链式调用
     */
    public function getPaginate(array $params): object
    {
        $search_default_key = isset($params['search_default_key']) ? $params['search_default_key'] : 'id';

        $where = isset($params['where']) ? $params['where'] : [];
        $without_field = isset($params['withoutField']) ? $params['withoutField'] : [];

        $search_keys = isset($params['search_keys']) ? $params['search_keys'] : [];
        $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : '';
        $search_like = isset($params['search_like']) ? $params['search_like'] : 0;

        $order_key = isset($params['order_key']) ? $params['order_key'] : '';
        $order_desc = isset($params['order_desc']) ? $params['order_desc'] : true;

        $page = isset($params['page']) ? $params['page'] : 1;
        $list_rows = isset($params['list_rows']) ? $params['list_rows'] : 15;

        return $this->where($where)
            ->search($search_default_key, $search_keys, $search_value, $search_like)
            ->withoutField($without_field)
            ->order($order_key, $order_desc)
            ->paginate($page, $list_rows);
    }

    /**
     * 设置模型
     * @param array $params 全部查询参数
     * @return object 返回当前对象，支持链式调用
     */
    public function getNoPaginate(array $params): object
    {
        $search_default_key = isset($params['search_default_key']) ? $params['search_default_key'] : 'id';

        $where = isset($params['where']) ? $params['where'] : [];
        $without_field = isset($params['withoutField']) ? $params['withoutField'] : [];

        $search_keys = isset($params['search_keys']) ? $params['search_keys'] : [];
        $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : '';
        $search_like = isset($params['search_like']) ? $params['search_like'] : 0;

        $order_key = isset($params['order_key']) ? $params['order_key'] : '';
        $order_desc = isset($params['order_desc']) ? $params['order_desc'] : true;

        $limit = isset($params['limit']) ? $params['limit'] : 15;

        return $this->where($where)
            ->search($search_default_key, $search_keys, $search_value, $search_like)
            ->withoutField($without_field)
            ->order($order_key, $order_desc)
            ->noPaginate($limit);
    }

    /**
     * 排除指定字段
     * @param array $withoutField 需要排除的字段
     * @return $this 返回当前对象，支持链式调用
     */
    private function withoutField($withoutField = []): self
    {
        // if ($withoutField !== []) {
        //     $pk = $this->model->getPk();
        //     // 1. count 时只查主键，排除大字段
        //     $this->query = $this->query->field($pk);
        //     // 2. 真正取数据时再 withoutField
        //     $this->query = $this->query->withoutField($withoutField);
        // }
        if ($withoutField !== []) {
            $this->query = $this->query->withoutField($withoutField);
        }
        return $this;
    }

    /**
     * 添加查询条件
     * 根据传入的条件数组进行查询
     * @param array $where 查询条件数组
     * @return $this 返回当前对象，支持链式调用
     */
    private function where($where = []): self
    {
        // 没搜索值直接返回当前对象
        if ($where == []) {
            return $this;
        }

        $this->query = $this->query->where($where);

        return $this;
    }

    /**
     * 搜索[AI优化]
     * 根据传入的搜索字段和值进行搜索
     * @param string $key 默认搜索字段
     * @param array $search_keys 搜索字段数组
     * @param string|null $search_value 搜索值
     * @param int $search_like 搜索模式 0前缀匹配|1后缀匹配|2模糊匹配
     * @return $this 返回当前对象，支持链式调用
     */
    private function search(string $key = 'id', array  $search_keys = [], string|null $search_value = '', int    $search_like = 0): self
    {
        // 没搜索值直接返回当前对象
        if ($search_value === '') {
            return $this;
        }

        //  激活空值搜索
        if ($search_value === null) {
            $fields = $search_keys ?: [$key]; // 没有指定字段就用默认字段
            $this->query = $this->query->where(function ($q) use ($fields) {
                foreach ($fields as $f) {
                    $q->where(function ($q) use ($f) {
                        $q->whereNull($f)->whereOr($f, '');
                    }, null, 'OR');
                }
            });
            return $this;
        }

        // 激活模糊搜索
        $sv  = addcslashes($search_value, '%_');
        $map = match ($search_like) {
            1 => '%' . $sv,
            2 => '%' . $sv . '%',
            default => $sv . '%',
        };

        if ($search_keys) {                       // 多字段
            $this->query = $this->query->where(function ($q) use ($search_keys, $map) {
                foreach ($search_keys as $i => $f) {
                    $method = $i === 0 ? 'whereLike' : 'orWhereLike';
                    $q->$method($f, $map);
                }
            });
        } else {                                  // 单字段
            $this->query = $this->query->whereLike($key, $map);
        }

        return $this;
    }

    /**
     * 排序
     * 根据传入的排序字段和排序方式进行排序
     * @param bool $desc 默认排序方式，true为降序，false为升序
     * @param string $order_key 排序字段
     * @param bool $order_desc 排序方式，true为降序，false为升序
     * @return $this 返回当前对象，支持链式调用
     */
    private function order(string $order_key = '', $order_desc = true): self
    {
        // 默认排序规则
        $rule = filter_var($order_desc, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        // 如果提供了 order_key，则使用 order_key 进行排序
        if ($order_key !== '') {
            // 如果提供了 order_desc，则根据 order_desc 确定排序规则
            $this->query = $this->query->order($order_key, $rule);
        } else {
            // 如果没有提供 order_key，但提供了 order_desc，则使用默认字段 'id' 进行排序
            $this->query = $this->query->order('id', $rule);
        }

        return $this;
    }

    /**
     * 分页
     * 根据传入的分页参数进行分页查询
     * @param int $page 默认页码
     * @param int $list_rows 默认每页记录数
     * @return mixed 查询结果
     */
    private function paginate(int $page = 1, int $list_rows = 15): object
    {
        $result = $this->query->paginate([
            'list_rows' => $list_rows,
            'page' => $page,
        ]);
        //dd($this->query->getLastSql());
        return $result;
    }

    /**
     * 不分页
     * 根据传入的限制参数进行查询
     * @param int $limit 限制查询的记录数
     * @return mixed 查询结果
     */
    private function noPaginate(int $limit = 15): object
    {
        return $this->query->limit($limit)->select();
    }
}
