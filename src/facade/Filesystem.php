<?php
declare (strict_types = 1);

namespace apixx\filesystem\facade;

use think\Facade;
use apixx\filesystem\Driver;

/**
 * Class Filesystem
 * @package think\facade
 * @mixin \apixx\filesystem\Filesystem
 * @method static Driver disk(?string $name = null)
 * @method static mixed getConfig(?string $name = null, mixed $default = null) 获取缓存配置
 * @method static array getDiskConfig(string $disk, ?string $name = null, mixed $default = null) 获取磁盘配置
 * @method static string|null getDefaultDriver() 默认驱动
 */
class Filesystem extends Facade
{
    protected static function getFacadeClass()
    {
        return \apixx\filesystem\Filesystem::class;
    }
}
