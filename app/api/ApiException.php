<?php

namespace app\api;

use \Throwable;
use app\api\ApiResponse;

class ApiException extends \Exception
{
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // 码段分配
    // 通用 0xxx | 用户 1xxx | 卡片 2xxx | 评论 3xxx | 标签 4xxx | 权限 5xxx | 文件 6xxx | 系统 9xxx
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    // 通用
    public const CODE_UNKNOWN = 9999;

    // 用户 10xx
    public const CODE_USER_NOT_FOUND      = 1001;
    public const CODE_USER_BANNED         = 1002;
    public const CODE_PASSWORD_MISMATCH   = 1003;
    public const CODE_USER_ALREADY_EXISTS = 1004;
    public const CODE_LOGIN_FAILED        = 1005;
    public const CODE_TOKEN_INVALID       = 1006;
    public const CODE_TOKEN_EXPIRED       = 1007;

    // 权限 11xx
    public const CODE_PERMISSION_DENIED          = 1101;
    public const CODE_VISITOR_MODE_DISABLED      = 1102;
    public const CODE_ROLE_NOT_FOUND             = 1103;
    public const CODE_ROLE_IN_USE                = 1104;

    // 卡片 20xx
    public const CODE_CARD_NOT_FOUND = 2001;
    public const CODE_CARD_DISABLED  = 2002;
    public const CODE_CARD_HIDDEN    = 2003;
    public const CODE_CARD_TOP_LIMIT = 2004;

    // 评论 30xx
    public const CODE_COMMENT_NOT_FOUND = 3001;
    public const CODE_COMMENT_DISABLED  = 3002;

    // 标签 40xx
    public const CODE_TAG_NOT_FOUND      = 4001;
    public const CODE_TAG_NAME_DUPLICATE = 4002;

    // 权限 50xx
    public const CODE_PERMISSION_NOT_FOUND = 5001;

    // 文件 60xx
    public const CODE_FILE_TOO_LARGE        = 6001;
    public const CODE_FILE_TYPE_NOT_ALLOWED = 6002;
    public const CODE_FILE_UPLOAD_FAILED    = 6003;

    // 系统 9xxx
    public const CODE_SYSTEM_ERROR          = 9001;
    public const CODE_PARAM_INVALID         = 9002;
    public const CODE_RESOURCE_NOT_FOUND    = 9003;

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // 码→消息 / HTTP 映射
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    private const CODE_MESSAGE_MAP = [
        self::CODE_USER_NOT_FOUND      => '用户不存在',
        self::CODE_USER_BANNED         => '账户已被封禁或未激活',
        self::CODE_PASSWORD_MISMATCH   => '密码不匹配',
        self::CODE_USER_ALREADY_EXISTS => '用户已存在',
        self::CODE_LOGIN_FAILED        => '登录失败',
        self::CODE_TOKEN_INVALID       => '令牌无效',
        self::CODE_TOKEN_EXPIRED       => '令牌已过期',
        self::CODE_PERMISSION_DENIED   => '权限不足',
        self::CODE_VISITOR_MODE_DISABLED => '访客模式未开启',
        self::CODE_ROLE_NOT_FOUND      => '角色不存在',
        self::CODE_ROLE_IN_USE         => '角色使用中无法删除',
        self::CODE_CARD_NOT_FOUND      => '卡片不存在',
        self::CODE_CARD_DISABLED       => '卡片已被禁用',
        self::CODE_CARD_HIDDEN         => '卡片已隐藏',
        self::CODE_CARD_TOP_LIMIT      => '置顶卡片数量已达上限',
        self::CODE_COMMENT_NOT_FOUND   => '评论不存在',
        self::CODE_COMMENT_DISABLED    => '评论已被删除',
        self::CODE_TAG_NOT_FOUND       => '标签不存在',
        self::CODE_TAG_NAME_DUPLICATE  => '标签名已存在',
        self::CODE_PERMISSION_NOT_FOUND => '权限不存在',
        self::CODE_FILE_TOO_LARGE      => '文件大小超出限制',
        self::CODE_FILE_TYPE_NOT_ALLOWED => '文件类型不允许',
        self::CODE_FILE_UPLOAD_FAILED  => '文件上传失败',
        self::CODE_SYSTEM_ERROR         => '系统异常',
        self::CODE_PARAM_INVALID        => '参数错误',
        self::CODE_RESOURCE_NOT_FOUND   => '资源不存在',
        self::CODE_UNKNOWN              => '未知错误',
    ];

    private const CODE_HTTP_MAP = [
        self::CODE_USER_NOT_FOUND       => 401,
        self::CODE_USER_BANNED          => 403,
        self::CODE_PASSWORD_MISMATCH     => 401,
        self::CODE_USER_ALREADY_EXISTS   => 409,
        self::CODE_LOGIN_FAILED          => 401,
        self::CODE_TOKEN_INVALID         => 401,
        self::CODE_TOKEN_EXPIRED         => 401,
        self::CODE_PERMISSION_DENIED     => 403,
        self::CODE_VISITOR_MODE_DISABLED => 403,
        self::CODE_ROLE_NOT_FOUND        => 404,
        self::CODE_ROLE_IN_USE           => 409,
        self::CODE_CARD_NOT_FOUND        => 404,
        self::CODE_CARD_DISABLED         => 403,
        self::CODE_CARD_HIDDEN           => 403,
        self::CODE_COMMENT_NOT_FOUND     => 404,
        self::CODE_COMMENT_DISABLED      => 403,
        self::CODE_TAG_NOT_FOUND         => 404,
        self::CODE_TAG_NAME_DUPLICATE    => 409,
        self::CODE_PERMISSION_NOT_FOUND  => 404,
        self::CODE_PARAM_INVALID         => 400,
        self::CODE_RESOURCE_NOT_FOUND    => 404,
        self::CODE_FILE_TOO_LARGE        => 413,
        self::CODE_FILE_TYPE_NOT_ALLOWED  => 415,
        self::CODE_FILE_UPLOAD_FAILED    => 500,
        self::CODE_SYSTEM_ERROR           => 500,
        self::CODE_UNKNOWN               => 500,
    ];

    protected $data;
    protected $httpStatus;

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // 构造函数
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function __construct(
        string $message = "",
        int $code = self::CODE_UNKNOWN,
        mixed $data = null,
        ?Throwable $previous = null
    ) {
        $this->data = $data;
        $this->httpStatus = self::CODE_HTTP_MAP[$code] ?? 500;
        parent::__construct($message, $code, $previous);
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // 静态工厂
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public static function badRequest(
        string $message = "",
        int $code = self::CODE_PARAM_INVALID,
        mixed $data = null,
        ?Throwable $previous = null
    ): self {
        return new self($message, $code, $data, $previous);
    }

    public static function unauthorized(
        string $message = "",
        int $code = self::CODE_LOGIN_FAILED,
        mixed $data = null,
        ?Throwable $previous = null
    ): self {
        return new self($message, $code, $data, $previous);
    }

    public static function forbidden(
        string $message = "",
        int $code = self::CODE_PERMISSION_DENIED,
        mixed $data = null,
        ?Throwable $previous = null
    ): self {
        return new self($message, $code, $data, $previous);
    }

    public static function notFound(
        string $message = "",
        int $code = self::CODE_RESOURCE_NOT_FOUND,
        mixed $data = null,
        ?Throwable $previous = null
    ): self {
        return new self($message, $code, $data, $previous);
    }

    public static function error(
        string $message = "",
        int $code = self::CODE_SYSTEM_ERROR,
        mixed $data = null,
        ?Throwable $previous = null
    ): self {
        return new self($message, $code, $data, $previous);
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // 辅助方法
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function getData(): mixed
    {
        return $this->data;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public static function getMessageByCode(int $code): string
    {
        return self::CODE_MESSAGE_MAP[$code] ?? self::CODE_MESSAGE_MAP[self::CODE_UNKNOWN];
    }

    public static function getHttpStatusByCode(int $code): int
    {
        return self::CODE_HTTP_MAP[$code] ?? 500;
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // 异常 → 响应
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public function exceptionHandle(): \think\Response
    {
        $httpStatus = $this->getHttpStatus();
        $code = $this->getCode();
        $message = $this->message ?: self::getMessageByCode($code);
        $detail = $this->data;

        $data = ApiResponse::error($message, $detail, $code);
        $result = \think\Response::create($data, 'json')->code($httpStatus);
        return ApiResponse::setHeader($result);
    }
}
