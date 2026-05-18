<?php

namespace app\api\service\Storage\Contract;

interface HasPresignedUrl
{
    public function getPresignedUrl(string $driverPath, int $expire = 3600): string;
}
