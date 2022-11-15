<?php
declare( strict_types = 1 );

namespace apixx\filesystem\driver;
use apixx\filesystem\Driver;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;

class Local extends Driver
{
    /**
     * 配置参数
     * @var array
     */
    protected $config = [
        'root' => '',
    ];

    protected function createAdapter()
    {
        $visibility = PortableVisibilityConverter::fromArray(
            $this->config['permissions'] ?? [],
            $this->config['visibility'] ?? Visibility::PRIVATE
        );

        $links = ($this->config['links'] ?? null) === 'skip'? LocalFilesystemAdapter::SKIP_LINKS : LocalFilesystemAdapter::DISALLOW_LINKS;
        
        return new LocalFilesystemAdapter(
            $this->config['root'],
            $visibility,
            $this->config['lock'] ?? LOCK_EX,
            $links
        );
    }

    /**
     * 获取文件访问地址
     * @param string $path 文件路径
     * @return string
     */
    public function url(string $path): string
    {
        $path = str_replace('\\', '/', $path);

        if (isset($this->config['url'])) {
            return $this->concatPathToUrl($this->config['url'], $path);
        }
        return parent::url($path);
    }
}
