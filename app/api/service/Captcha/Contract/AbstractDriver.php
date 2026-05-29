<?php

namespace app\api\service\Captcha\Contract;

abstract class AbstractDriver implements CaptchaInterface
{
    protected string $slug;
    protected array $config;
    protected string $driverType;

    public function __construct(string $slug, array $config, string $driverType)
    {
        $this->slug = $slug;
        $this->config = $config;
        $this->driverType = $driverType;
    }

    public static function meta(): array
    {
        $shortName = (new \ReflectionClass(static::class))->getShortName();
        $slug = strtolower(str_replace('Driver', '', $shortName));

        return [
            'slug'   => $slug,
            'type'   => static::type(),
            'name'   => static::class,
            'icon'   => 'mdi-shield-check-outline',
            'fields' => [],
        ];
    }
}
