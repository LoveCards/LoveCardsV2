<?php

namespace app\api\service\Captcha\Driver;

use app\api\service\Captcha\Contract\AbstractDriver;
use app\common\infra\CacheManager;

class ImageDriver extends AbstractDriver
{
    private const WIDTH      = 120;
    private const HEIGHT     = 40;
    private const LENGTH     = 4;
    private const TTL        = 120;
    private const FONT_SIZE  = 20;
    private const CHAR_SET   = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    public static function type(): string
    {
        return 'captcha';
    }

    public static function meta(): array
    {
        return [
            'slug'   => 'gd_image',
            'type'   => 'captcha',
            'name'   => '图片验证码',
            'icon'   => 'mdi-image-filter-none',
            'fields' => [],
        ];
    }

    public function generate(array $params): array
    {
        $imageId = bin2hex(random_bytes(8));
        $code    = $this->createCode(self::LENGTH);

        CacheManager::set('captcha', 'Captcha_image_' . $imageId, strtoupper($code), self::TTL);

        $imageBase64 = $this->renderImage($code);

        return [
            'image_id'    => $imageId,
            'image_base64' => $imageBase64,
            'driver'      => 'gd_image',
        ];
    }

    public function verify(array $params): bool
    {
        $imageId = $params['image_id'] ?? '';
        $code    = $params['code'] ?? '';

        if (empty($imageId) || empty($code)) {
            return false;
        }

        $cached = CacheManager::get('captcha', 'Captcha_image_' . $imageId);

        if ($cached && strtoupper($cached) === strtoupper($code)) {
            CacheManager::delete('Captcha_image_' . $imageId);
            return true;
        }

        return false;
    }

    private function createCode(int $length): string
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= self::CHAR_SET[random_int(0, strlen(self::CHAR_SET) - 1)];
        }
        return $code;
    }

    private function renderImage(string $code): string
    {
        $width  = self::WIDTH;
        $height = self::HEIGHT;

        $image = imagecreatetruecolor($width, $height);

        $bgColor = imagecolorallocate($image, random_int(220, 255), random_int(220, 255), random_int(220, 255));
        imagefill($image, 0, 0, $bgColor);

        for ($i = 0; $i < 5; $i++) {
            $lineColor = imagecolorallocate($image, random_int(100, 200), random_int(100, 200), random_int(100, 200));
            imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $lineColor);
        }

        for ($i = 0; $i < 50; $i++) {
            $dotColor = imagecolorallocate($image, random_int(100, 200), random_int(100, 200), random_int(100, 200));
            imagesetpixel($image, random_int(0, $width), random_int(0, $height), $dotColor);
        }

        $charWidth = (int) ($width / (self::LENGTH + 1));
        for ($i = 0; $i < strlen($code); $i++) {
            $textColor = imagecolorallocate($image, random_int(0, 100), random_int(0, 100), random_int(0, 100));
            $x = $charWidth * $i + (int) ($charWidth * 0.2);
            $y = random_int((int)($height * 0.6), (int)($height * 0.8));
            $angle = random_int(-15, 15);
            imagestring($image, 5, $x, $y - 15, $code[$i], $textColor);
        }

        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,' . base64_encode($imageData);
    }
}
