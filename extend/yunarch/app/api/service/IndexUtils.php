<?php

namespace yunarch\app\api\service;

use think\Model;

/**
 * 通用模型超级分页查询类
 * 提供基于模型的灵活分页查询功能，支持条件筛选、字段过滤、排序等操作
 */
class IndexUtils
{
    /**
     * 传入的模型类名
     * @var string
     */
    private $model;

    /**
     * 传入的查询参数
     * @var array
     */
    private $params;
    // $params = [
    //     'page' => Request::param('page', 0),
    //     'list_rows' => Request::param('list_rows', 12),
    //     'search_value' => Request::param('search_value'),
    //     'search_keys' => UtilsCommon::isJson(Request::param('search_keys')),
    //     'order_desc' => Request::param('order_desc'),
    //     'order_key' => Request::param('order_key')
    // ];

    /**
     * 查询对象
     * @var Model
     */
    private $query;

    /**
     * 构造函数
     * 初始化模型类名和查询参数，并创建查询对象
     * @param string $model 模型类名
     * @param array $params 查询参数
     */
    function __construct(string $model, $params)
    {
        $this->model = new $model();
        $this->params = $params;
        $this->query = $this->model;

        //默认值设置
        $this->params['page'] = isset($this->params['page']) ? $this->params['page'] : 1;
        $this->params['list_rows'] = isset($this->params['list_rows']) ? $this->params['list_rows'] : 12;
    }

    /**
     * 执行通用查询逻辑
     * 包括条件筛选、字段过滤、排序和分页
     * @param string|null $where_default_key 默认筛选字段
     * @param array $withoutField 需要排除的字段
     * @param bool|null $order_default_desc 默认排序是否为降序
     * @return mixed 查询结果
     */
    public function common($where_default_key = null, $withoutField = [], $order_default_desc = null)
    {
        return $this->where($where_default_key)
            ->withoutField($withoutField)
            ->order($order_default_desc)
            ->paginate();
    }

    /**
     * 执行不分页查询逻辑
     * 包括条件筛选、字段过滤、排序
     * @param string|null $where_default_key 默认筛选字段
     * @param array $withoutField 需要排除的字段
     * @param bool|null $order_default_desc 默认排序是否为降序
     * @return mixed 查询结果
     */
    public function noPaginate($where_default_key = null, $withoutField = [], $order_default_desc = null)
    {
        return $this->where($where_default_key)
            ->withoutField($withoutField)
            ->order($order_default_desc)
            ->query->select();
    }

    /**
     * 排除指定字段
     * @param array $withoutField 需要排除的字段
     * @return $this 返回当前对象，支持链式调用
     */
    private function withoutField($withoutField = [])
    {
        if ($withoutField !== []) {
            $this->query = $this->query->withoutField($withoutField);
        }
        return $this;
    }

    /**
     * 条件筛选
     * 根据传入的搜索关键字和值进行条件筛选
     * @param string $default_key 默认筛选字段
     * @return $this 返回当前对象，支持链式调用
     */
    private function where($default_key = "id")
    {
        // 没搜索值直接返回当前对象
        if (!isset($this->params["search_value"])) {
            return $this;
        }

        // 激活搜索
        if (isset($this->params['search_keys'])) {
            $searchKey = $this->params['search_keys'];
            $map = [];
            foreach ($searchKey as $key => $value) {
                $map[] = [$value, 'like', '%' . $this->params['search_value'] . '%'];
            }
            $this->query = $this->query->whereOr($map);
        } else {
            $this->query = $this->query->where($default_key, 'like', '%' . $this->params['search_value'] . '%');
        }

        return $this;
    }

    /**
     * 排序
     * 根据传入的排序字段和规则进行排序
     * @param bool|null $default_desc 默认排序是否为降序
     * @return $this 返回当前对象，支持链式调用
     */
    private function order($default_desc = false)
    {
        // 默认排序规则
        $rule = $default_desc ? 'desc' : 'asc';

        // 如果提供了 order_key，则使用 order_key 进行排序
        if (isset($this->params['order_key'])) {
            // 如果提供了 order_desc，则根据 order_desc 确定排序规则
            if (isset($this->params['order_desc'])) {
                $rule = filter_var($this->params['order_desc'], FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
            }
            $this->query = $this->query->order($this->params['order_key'], $rule);
        } else {
            // 如果没有提供 order_key，但提供了 order_desc，则使用默认字段 'id' 进行排序
            if (isset($this->params['order_desc'])) {
                $rule = filter_var($this->params['order_desc'], FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';
            }
            $this->query = $this->query->order('id', $rule);
        }

        return $this;
    }

    /**
     * 分页
     * 根据传入的分页参数进行分页查询
     * @return mixed 分页结果
     */
    private function paginate()
    {
        return $this->query->paginate([
            'list_rows' => $this->params['list_rows'],
            'page' => $this->params['page'],
        ]);
    }
}
