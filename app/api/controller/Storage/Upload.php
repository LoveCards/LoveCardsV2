<?php

namespace app\api\controller\Storage;

use think\facade\Request;
use app\api\ApiResponse;
use app\api\ApiException;
use app\api\service\Storage\StorageManager;
use app\api\service\Storage\DirectUploadManager;
use app\api\service\Storage\ChannelManager;
use app\api\service\Storage\PathGenerator;
use app\api\service\User\Users as UsersService;
use app\api\validate\Files as FilesValidate;
use app\api\controller\BaseController;

class Upload extends BaseController
{
    private function isAdmin(): bool
    {
        $uid = request()->uid ?? 0;
        if ($uid <= 0) return false;
        $user = UsersService::Get($uid);
        if (!$user || !$user->id) return false;
        $roles = is_array($user->roles_id) ? $user->roles_id : (json_decode($user->roles_id, true) ?: []);
        return in_array(config('system.system_roles.root'), $roles) || in_array(config('system.system_roles.admin'), $roles);
    }

    public function upload()
    {
        $file = request()->file('file');
        if (empty($file)) {
            throw ApiException::badRequest('请提交文件');
        }

        $userId = request()->uid ?? 0;

        if (!StorageManager::checkRateLimit((string) $userId)) {
            throw ApiException::tooMany('请求过于频繁');
        }

        $scene = Request::param('scene', 'direct');
        $refType = Request::param('ref_type', null);
        $refId = Request::param('ref_id', null);
        $isPublic = (int) Request::param('is_public', 0);

        $validateData = ['scene' => $scene, 'is_public' => $isPublic];
        if ($refType !== null) $validateData['ref_type'] = $refType;
        if ($refId !== null) $validateData['ref_id'] = $refId;

        $validate = new FilesValidate();
        if (!$validate->check($validateData)) {
            throw ApiException::badRequest($validate->getError());
        }

        $status = 0;
        $uploadStatus = 1;

        $path = PathGenerator::generate(ChannelManager::getDefaultChannel(), $file->getOriginalName());

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
    }

    public function list()
    {
        $params = $this->paramIndex(Request::param());

        $params['show_deleted'] = Request::param('show_deleted', 0);
        $params['status'] = Request::param('status', null);
        $params['upload_status'] = Request::param('upload_status', null);
        $params['scene'] = Request::param('scene', null);

        $userId = request()->uid ?? 0;
        $isAdmin = $this->isAdmin();

        $result = StorageManager::list($params, $userId, $isAdmin);
        return ApiResponse::createOk($result);
    }

    public function get($id = 0)
    {
        $fileId = (int) ($id ?: Request::param('id', 0));
        $userId = request()->uid ?? 0;
        $isAdmin = $this->isAdmin();

        $file = StorageManager::getFile($fileId, $userId, $isAdmin);
        if (!$file) {
            throw ApiException::notFound('文件不存在');
        }
        return ApiResponse::createOk($file);
    }

    public function batch()
    {
        if (!$this->isAdmin()) {
            return ApiResponse::createForbidden('需要管理员权限');
        }

        $idsParam = Request::param('ids', '[]');
        $ids = is_string($idsParam) ? json_decode($idsParam, true) : $idsParam;
        $method = Request::param('method', '');

        $validate = new FilesValidate();
        if (!$validate->check(['method' => $method])) {
            return ApiResponse::createBadRequest($validate->getError());
        }

        if (empty($ids) || empty($method)) {
            return ApiResponse::createBadRequest('参数不完整');
        }

        try {
            StorageManager::batchOperate($method, $ids);
            return ApiResponse::createNoContent();
        } catch (\Throwable $e) {
            throw ApiException::error($e->getMessage());
        }
    }

    public function direct()
    {
        $userId = request()->uid ?? 0;

        if (!StorageManager::checkRateLimit((string) $userId)) {
            throw ApiException::tooMany('请求过于频繁');
        }

        $filename = Request::param('filename', '');
        $size = (int) Request::param('size', 0);
        $mime = Request::param('mime', '');

        $path = PathGenerator::generate(ChannelManager::getDefaultChannel(), $filename);

        $result = DirectUploadManager::createPendingRecord($filename, $mime, $size, $path, $userId);
        return ApiResponse::createOk($result);
    }

    public function confirm($id = 0)
    {
        $recordId = (int) ($id ?: Request::param('record_id', 0));

        $result = DirectUploadManager::confirmUpload($recordId);
        if (!$result) {
            throw ApiException::error('确认失败');
        }
        return ApiResponse::createNoContent();
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

    public function allDelete($id)
    {
        if (!$this->isAdmin()) {
            throw ApiException::forbidden('需要管理员权限');
        }

        StorageManager::hardDelete((int) $id);
        return ApiResponse::createNoContent();
    }
}
