<?php

namespace app\api\model;

use think\Model;
use think\model\concern\SoftDelete;

class Files extends Model
{
    use SoftDelete;

    protected $name = 'files';
    protected $pk = 'id';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = 'deleted_at';

    protected $hidden = ['deleted_at', 'driver_path'];

    const SCENE_CARD = 'card';
    const SCENE_COMMENT = 'comment';
    const SCENE_AVATAR = 'avatar';
    const SCENE_DIRECT = 'direct';

    // 审核状态（status 字段）
    const STATUS_NORMAL = 0;           // 正常
    const STATUS_BANNED = 1;           // 封禁

    // 上传状态（upload_status 字段）
    const UPLOAD_PENDING = 0;          // 上传中
    const UPLOAD_COMPLETED = 1;        // 上传完成
    const UPLOAD_FAILED = 2;           // 上传失败

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    public function scopeVisible($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->whereOr('is_public', 1);
        });
    }

    public function scopeByScene($query, $scene)
    {
        return $query->where('scene', $scene);
    }

    public function scopeByRef($query, $refType, $refId = null)
    {
        $query->where('ref_type', $refType);
        if ($refId !== null) {
            $query->where('ref_id', $refId);
        }
        return $query;
    }

    public function scopeNormal($query)
    {
        return $query->where('status', self::STATUS_NORMAL);
    }

    public function markAsCompleted(string $url, string $driverPath): bool
    {
        $this->status = self::STATUS_NORMAL;
        $this->upload_status = self::UPLOAD_COMPLETED;
        $this->file_url = $url;
        $this->driver_path = $driverPath;
        $this->updated_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    public function markAsFailed(): bool
    {
        $this->upload_status = self::UPLOAD_FAILED;
        $this->updated_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    public function isExpired(): bool
    {
        if ($this->expire_at === null) {
            return false;
        }
        return strtotime($this->expire_at) < time();
    }

    public static function cleanupExpired(int $limit = 100): array
    {
        $expired = self::where('upload_status', self::UPLOAD_PENDING)
            ->where('expire_at', '<', date('Y-m-d H:i:s'))
            ->limit($limit)
            ->select();

        $cleaned = [];
        foreach ($expired as $record) {
            $cleaned[] = [
                'id' => $record->id,
                'expired_at' => $record->expire_at,
            ];
            $record->upload_status = self::UPLOAD_FAILED;
            $record->save();
        }

        return $cleaned;
    }
}
