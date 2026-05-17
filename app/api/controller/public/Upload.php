<?php

namespace app\api\controller\public;

use think\facade\Request;
use app\api\controller\BaseController;
use app\api\ApiResponse;
use app\api\service\Storage\StorageManager;
use app\api\service\Storage\DirectUpload\DirectUploadManager;
use app\api\service\Storage\ChannelManager;
use app\api\service\Storage\PathGenerator;
use app\api\service\Users as UsersService;
use app\api\model\Files;
use app\api\Params;

class Upload extends BaseController
{
    protected $Params;

    function __construct()
    {
        parent::__construct();
        $this->Params = new Params();
    }

    private function isAdmin(): bool
    {
        if (isset($this->JWT_SESSION['aid'])) {
            return true;
        }
        $uid = $this->JWT_SESSION['uid'] ?? 0;
        if ($uid <= 0) return false;
        $user = UsersService::Get($uid);
        if (!$user || !$user->id) return false;
        $roles = json_decode($user->roles_id, true) ?: [];
        return in_array(1, $roles) || in_array(2, $roles);
    }

    public function upload()
    {
        $file = request()->file('file');
        if (empty($file)) {
            return ApiResponse::createError('请提交文件');
        }

        $userId = $this->JWT_SESSION['uid'] ?? 0;

        if (!StorageManager::checkRateLimit((string) $userId)) {
            return ApiResponse::createTooMany('请求过于频繁');
        }

        $scene = Request::param('scene', 'direct');
        $refType = Request::param('ref_type', null);
        $refId = Request::param('ref_id', null);
        $isPublic = (int) Request::param('is_public', 0);

        $status = Files::STATUS_NORMAL;
        $uploadStatus = Files::UPLOAD_COMPLETED;

        $path = PathGenerator::generate(ChannelManager::getDefaultChannel(), $file->getOriginalName());

        try {
            $result = StorageManager::upload($file, $path, [
                'user_id' => $userId,
                'scene' => $scene,
                'ref_type' => $refType,
                'ref_id' => $refId,
                'is_public' => $isPublic,
                'status' => $status,
                'upload_status' => $uploadStatus,
            ]);

            return ApiResponse::createOk($result->toArray());
        } catch (\Exception $e) {
            return ApiResponse::createError('上传失败', $e->getMessage());
        }
    }

    public function files()
    {
        $params = $this->Params->IndexParams(Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        // 这些字段不在 IndexParams scene filter 里，手动提取
        $params['show_deleted'] = Request::param('show_deleted', 0);
        $params['status'] = Request::param('status', null);
        $params['upload_status'] = Request::param('upload_status', null);
        $params['scene'] = Request::param('scene', null);

        $userId = $this->JWT_SESSION['uid'] ?? 0;
        $isAdmin = $this->isAdmin();

        $result = StorageManager::list($params, $userId, $isAdmin);
        return ApiResponse::createOk($result);
    }

    public function getFile()
    {
        $fileId = (int) Request::param('id', 0);
        $userId = $this->JWT_SESSION['uid'] ?? 0;
        $isAdmin = $this->isAdmin();

        $file = StorageManager::getFile($fileId, $userId, $isAdmin);
        return $file ? ApiResponse::createOk($file) : ApiResponse::createNotFound();
    }

    public function batchOperate()
    {
        if (!$this->isAdmin()) {
            return ApiResponse::createForbidden('需要管理员权限');
        }

        $ids = json_decode(Request::param('ids', '[]'), true);
        $method = Request::param('method', '');

        if (empty($ids) || empty($method)) {
            return ApiResponse::createBadRequest('参数不完整');
        }

        try {
            StorageManager::batchOperate($method, $ids);
            return ApiResponse::createNoContent();
        } catch (\Throwable $e) {
            return ApiResponse::createError($e->getMessage());
        }
    }

    public function getDirectUploadCredential()
    {
        $userId = $this->JWT_SESSION['uid'] ?? 0;

        if (!StorageManager::checkRateLimit((string) $userId)) {
            return ApiResponse::createError('请求过于频繁');
        }

        $filename = Request::param('filename', '');
        $size = (int) Request::param('size', 0);
        $mime = Request::param('mime', '');

        $path = PathGenerator::generate(ChannelManager::getDefaultChannel(), $filename);

        try {
            $result = DirectUploadManager::createPendingRecord($filename, $mime, $size, $path, $userId);
            return ApiResponse::createOk($result);
        } catch (\Exception $e) {
            return ApiResponse::createError('获取凭证失败', $e->getMessage());
        }
    }

    public function confirmDirectUpload()
    {
        $recordId = (int) Request::param('record_id', 0);

        $result = DirectUploadManager::confirmUpload($recordId);
        return $result ? ApiResponse::createOk() : ApiResponse::createError('确认失败');
    }

    public function cleanup()
    {
        if (!$this->isAdmin()) {
            return ApiResponse::createForbidden('需要管理员权限');
        }
        $limit = (int) Request::param('limit', 100);

        $cleaned = DirectUploadManager::cleanupExpired($limit);
        return ApiResponse::createOk(['cleaned' => count($cleaned)]);
    }
}
