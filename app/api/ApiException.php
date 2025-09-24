<?php

namespace app\api;

use \Throwable;
use app\api\ApiResponse;

class ApiException extends \Exception
{

    // | 家族                          | 具体类                        | 一句话场景               | 继承               |
    // | --------------------------- | -------------------------- | ------------------- | ---------------- |
    // | **逻辑异常** `LogicException`   | `BadFunctionCallException` | 调了根本不存在的函数/方法       | LogicException   |
    // |                             | `BadMethodCallException`   | 调了类里不存在的**方法**      | 同上               |
    // |                             | `DomainException`          | 值**超出业务定义域**（如开方负数） | 同上               |
    // |                             | `InvalidArgumentException` | 参数**非法**（类型对但值不对）   | 同上               |
    // |                             | `LengthException`          | 长度超限（字符串/数组）        | 同上               |
    // |                             | `OutOfRangeException`      | 索引/键**超出允许范围**      | 同上               |
    // | **运行异常** `RuntimeException` | `OutOfBoundsException`     | 运行期索引越界（读数组）        | RuntimeException |
    // |                             | `OverflowException`        | **满栈/满队列**再 push    | 同上               |
    // |                             | `RangeException`           | 值**溢出**（大整数、日期）     | 同上               |
    // |                             | `UnderflowException`       | **空栈/空队列**再 pop     | 同上               |
    // | **文件系统**                    | `DirectoryIterator` 相关     | 文件句柄失效              | RuntimeException |
    // | **迭代器**                     | `UnexpectedValueException` | 迭代器返回**类型与约定不符**    | RuntimeException |


    public const CODE_DEFAULT = 0;
    public const CODE_ERROR = 5000;

    protected $data;
    protected $handle;

    public function __construct(string $message = "", int $code = 0, mixed $data = null, ?Throwable $previous = null)
    {
        // 将附加数据赋值给类属性
        $this->data = $data;

        // 调用父类的构造函数，完成基础异常的初始化
        // 注意：父类构造函数的第三个参数是 $previous，我们调整了参数顺序以便于使用
        parent::__construct($message, $code, $previous);
    }

    /**
     * 创建一个默认错误
     *
     * @param string $message
     * @param mixed $data
     * @param Throwable|null|null $previous
     * @return void
     */
    public static function make(string $message = "", mixed $data = null, Throwable|null $previous = null)
    {
        return new self($message, self::CODE_DEFAULT, $data, $previous);
    }

    /**
     * 获取附加数据
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    //400
    public static function createBadRequest(string $message = "", mixed $data = null, Throwable|null $previous = null): self
    {
        $e = new self($message, self::CODE_DEFAULT, $data, $previous);
        $e->handle = 'createBadRequest';
        return $e;
    }
    //401
    public static function createUnauthorized(string $message = "", mixed $data = null, Throwable|null $previous = null): self
    {
        $e = new self($message, self::CODE_DEFAULT, $data, $previous);
        $e->handle = 'createUnauthorized';
        return $e;
    }
    //404
    public static function createNotFound(string $message = "", mixed $data = null, Throwable|null $previous = null): self
    {
        $e = new self($message, self::CODE_DEFAULT, $data, $previous);
        $e->handle = 'createNotFound';
        return $e;
    }
    //500
    public static function createError(string $message = "", mixed $data = null, Throwable|null $previous = null): self
    {
        $e = new self($message, self::CODE_DEFAULT, $data, $previous);
        $e->handle = 'createError';
        return $e;
    }

    //实现Api格式响应
    public function exceptionHandle()
    {
        $class = ApiResponse::class;
        $method = $this->handle;
        $message = $this->message;
        $detail = $this->data;
        return call_user_func([$class, $method], $message, $detail);
    }
}
