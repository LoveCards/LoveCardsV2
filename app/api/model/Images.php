<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

use app\common\Common;

class Images extends Model
{
    //开启软删除
    use SoftDelete;
    protected $deleteTime = 'deleted_at';

    //自动时间戳
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    // 设置字段信息
    protected $schema = [
        'id' => 'int',
        'aid' => 'int',
        'pid' => 'int',
        'uid' => 'int',
        'url' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // 默认排除字段
    protected static $withoutField = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    public static function Post($data)
    {
        $result = self::create($data);
        if ($result) {
            return Common::mArrayEasyReturnStruct('数据插入成功', true);
        }
        return Common::mArrayEasyReturnStruct('数据插入失败', false);
    }

    /**
     * 读取列表
     *
     * @return void
     */
    public static function Index($paginte)
    {
        $result = self::withoutField('password')->paginate([
            'list_rows' => $paginte['list_rows'],
            'page' => $paginte['page'],
        ]);
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result->toArray());
        }
        return Common::mArrayEasyReturnStruct('列表查询失败', false);
    }

    /**
     * 更新指定ID行
     *
     * @param int $id
     * @param array $data
     * @return void
     */
    public static function Patch($id, $data)
    {
        try {
            $result = self::update($data, ['id' => $id]);
            return Common::mArrayEasyReturnStruct(null, true, $result);
        } catch (\Throwable $th) {
            return Common::mArrayEasyReturnStruct('更新失败', false, $th);
        }
    }

    /**
     * 读取指定ID行
     *
     * @param int $id
     * @return array['status','msg','data'=>object]
     */
    public static function Get($id)
    {
        $withoutField = self::$withoutField;
        $withoutField[] = 'password';
        $result = self::where('id', $id)->withoutField($withoutField)->findOrEmpty();
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result);
        }
        return Common::mArrayEasyReturnStruct('查询失败', false, $result);
    }

    /**
     * 删除指定ID行
     *
     * @param int $id
     * @return void
     */
    public static function Del($id)
    {
        $result = self::delete($id);
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result);
        }
        return Common::mArrayEasyReturnStruct('删除失败', false);
    }
}
