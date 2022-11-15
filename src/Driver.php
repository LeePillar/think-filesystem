<?php
declare ( strict_types = 1 );

namespace apixx\filesystem;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\PathPrefixer;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToDeleteDirectory;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\Visibility;
use Psr\Http\Message\StreamInterface;
use think\Cache;
use think\File;
use think\file\UploadedFile;
use think\helper\Arr;

abstract class Driver
{

    /** @var Cache */
    protected $cache;

    /** @var Filesystem */
    protected $filesystem;

    protected $adapter;

    /** @var PathPrefixer */
    protected $prefixer;

    /**
     * 配置参数
     * @var array
     */
    protected $config = [];

    public function __construct(Cache $cache,array $config)
    {
        $this->cache  = $cache;
        $this->config = array_merge( $this->config,$config );

        $separator      = $config['directory_separator'] ?? DIRECTORY_SEPARATOR;
        $this->prefixer = new PathPrefixer( $config['root'] ?? '',$separator );

        if (isset( $config['prefix'] )) {
            $this->prefixer = new PathPrefixer( $this->prefixer->prefixPath( $config['prefix'] ),$separator );
        }

        $this->adapter    = $this->createAdapter();
        $this->filesystem = $this->createFilesystem( $this->adapter,$this->config );
    }

    abstract protected function createAdapter();

    /**
     * @param FilesystemAdapter $adapter
     * @param array $config
     * @return Filesystem
     */
    protected function createFilesystem(FilesystemAdapter $adapter,array $config)
    {
        return new Filesystem( $adapter,Arr::only( $config,[
            'directory_visibility',
            'temporary_url',
            'visibility',
            'disable_asserts',
            'url'
        ] ) );
    }

    /**
     * 获取文件完整路径
     * @param string $path
     * @return string
     */
    public function path(string $path): string
    {
        return $this->prefixer->prefixPath( $path );
    }

    protected function concatPathToUrl($url,$path)
    {
        return rtrim( $url,'/' ).'/'.ltrim( $path,'/' );
    }

    /**
     * 判断文件和目录是否存在
     * @param string $path
     * @return bool
     */
    public function exists($path): bool
    {
        return $this->filesystem->has( $path );
    }

    /**
     * 确定文件或目录是否丢失。
     * @param string $path
     * @return bool
     */
    public function missing($path): bool
    {
        return !$this->exists( $path );
    }

    /**
     * 确定文件是否存在
     * @param string $path
     * @return bool
     */
    public function fileExists($path): bool
    {
        return $this->filesystem->fileExists( $path );
    }

    /**
     * Determine if a file is missing.
     *
     * @param string $path
     * @return bool
     */
    public function fileMissing($path): bool
    {
        return !$this->fileExists( $path );
    }

    /**
     * Determine if a directory exists.
     *
     * @param string $path
     * @return bool
     */
    public function directoryExists($path)
    {
        return $this->filesystem->directoryExists( $path );
    }

    /**
     * Determine if a directory is missing.
     *
     * @param string $path
     * @return bool
     */
    public function directoryMissing($path)
    {
        return !$this->directoryExists( $path );
    }

    /**
     * Get the contents of a file.
     *
     * @param string $path
     * @return string|null
     */
    public function get($path)
    {
        try {
            return $this->filesystem->read( $path );
        } catch ( UnableToReadFile $e ) {
            throw_if( $this->throwsExceptions(),$e );
        }
    }

    /**
     * Get the visibility for the given path.
     *
     * @param string $path
     * @return string
     */
    public function getVisibility($path)
    {
        if ($this->filesystem->visibility( $path ) == Visibility::PUBLIC) {
            return 'public';
        }

        return 'private';
    }

    /**
     * Set the visibility for the given path.
     *
     * @param string $path
     * @param string $visibility
     * @return bool
     */
    public function setVisibility($path,$visibility)
    {
        try {
            $this->filesystem->setVisibility( $path,$visibility );
        } catch ( UnableToSetVisibility $e ) {
            throw_if( $this->throwsExceptions(),$e );

            return false;
        }

        return true;
    }


    /**
     * 删除给定路径下的文件。
     * @param string|array $paths
     * @return bool
     */
    public function delete($paths)
    {
        $paths = is_array( $paths ) ? $paths : func_get_args();
        $success = true;
        foreach ( $paths as $path ) {
            try {
                $this->filesystem->delete( $path );
            } catch ( UnableToDeleteFile $e ) {
                throw_if( $this->throwsExceptions(),$e );
                $success = false;
            }
        }
        return $success;
    }

    /**
     * 将文件复制到新位置。
     * @param string $from
     * @param string $to
     * @return bool
     */
    public function copy($from,$to)
    {
        try {
            $this->filesystem->copy( $from,$to );
        } catch ( UnableToCopyFile $e ) {
            throw_if( $this->throwsExceptions(),$e );
            return false;
        }

        return true;
    }

    /**
     * Move a file to a new location.
     *
     * @param string $from
     * @param string $to
     * @return bool
     */
    public function move($from,$to)
    {
        try {
            $this->filesystem->move( $from,$to );
        } catch ( UnableToMoveFile $e ) {
            throw_if( $this->throwsExceptions(),$e );

            return false;
        }

        return true;
    }

    /**
     * Get the file size of a given file.
     *
     * @param string $path
     * @return int
     * @throws FilesystemException
     */
    public function size($path)
    {
        return $this->filesystem->fileSize( $path );
    }

    /**
     * Get the mime-type of a given file.
     *
     * @param string $path
     * @return string|false
     */
    public function mimeType($path)
    {
        try {
            return $this->filesystem->mimeType( $path );
        } catch ( UnableToRetrieveMetadata $e ) {
            throw_if( $this->throwsExceptions(),$e );
        }

        return false;
    }

    /**
     * Get the file's last modification time.
     *
     * @param string $path
     * @return int
     */
    public function lastModified($path): int
    {
        return $this->filesystem->lastModified( $path );
    }


    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        try {
            return $this->filesystem->readStream( $path );
        } catch ( UnableToReadFile $e ) {
            throw_if( $this->throwsExceptions(),$e );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream($path,$resource,array $options = [])
    {
        try {
            $this->filesystem->writeStream( $path,$resource,$options );
        } catch ( UnableToWriteFile|UnableToSetVisibility $e ) {
            throw_if( $this->throwsExceptions(),$e );

            return false;
        }

        return true;
    }

    protected function getLocalUrl($path)
    {
        if (isset( $this->config['url'] )) {
            return $this->concatPathToUrl( $this->config['url'],$path );
        }

        return $path;
    }

    public function url(string $path): string
    {
        $adapter = $this->adapter;
        if (method_exists( $adapter,'getUrl' )) {
            return $adapter->getUrl( $path );
        } elseif (method_exists( $this->filesystem,'getUrl' )) {
            return $this->filesystem->getUrl( $path );
        } elseif ($adapter instanceof LocalFilesystemAdapter) {
            return $this->getLocalUrl( $path );
        } else {
            throw new \RuntimeException( 'This driver does not support retrieving URLs.' );
        }
    }

    /**
     * Get the Flysystem driver.
     * @return \League\Flysystem\FilesystemOperator
     */
    public function getDriver()
    {
        return $this->filesystem;
    }

    /**
     * Get the Flysystem adapter.
     * @return \League\Flysystem\FilesystemAdapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * 保存文件
     * @param string $path 路径
     * @param File|string $file 文件
     * @param null|string|\Closure $rule 文件名规则
     * @param array $options 参数
     * @return bool|string
     */
    public function putFile(string $path,$file,$rule = null,array $options = [])
    {
        $file = is_string( $file ) ? new File( $file ) : $file;
        return $this->putFileAs( $path,$file,$file->hashName( $rule ),$options );
    }

    /**
     * 指定文件名保存文件
     * @param string $path 路径
     * @param File $file 文件
     * @param string $name 文件名
     * @param array $options 参数
     * @return bool|string
     */
    public function putFileAs(string $path,File $file,string $name,array $options = [])
    {
        $stream = fopen( $file->getRealPath(),'r' );
        $path   = trim( $path.'/'.$name,'/' );
        $result = $this->put( $path,$stream,$options );
        if (is_resource( $stream )) {
            fclose( $stream );
        }
        return $result ? $path : false;
    }

    /**
     * Undocumented function
     *
     * @param [type] $path
     * @param [type] $contents
     * @param array $options
     * @return void
     */
    public function put($path,$contents,$options = [])
    {
        $options = is_string( $options )? ['visibility' => $options]: (array)$options;
        if ($contents instanceof File ||
            $contents instanceof UploadedFile) {
            return $this->putFile( $path,$contents,$options );
        }
        try {
            if ($contents instanceof StreamInterface) {
                $this->writeStream( $path,$contents->detach(),$options );
                return true;
            }
            is_resource( $contents ) ? $this->writeStream( $path,$contents,$options ) : $this->write( $path,$contents,$options );
        } catch ( UnableToWriteFile|UnableToSetVisibility $e ) {
            throw_if( $this->throwsExceptions(),$e );
            return false;
        }
        return true;
    }

    /**
     * 获取一个目录中所有文件的数组。
     * @param string|null $directory
     * @param bool $recursive
     * @return array
     */
    public function files($directory = null,$recursive = false)
    {
        return $this->filesystem->listContents( $directory ?? '',$recursive )
            ->filter( function (StorageAttributes $attributes) {
                return $attributes->isFile();
            } )
            ->sortByPath()
            ->map( function (StorageAttributes $attributes) {
                return $attributes->path();
            } )
            ->toArray();
    }

    /**
     * 从给定目录中获取所有文件(递归)。
     * @param string|null $directory
     * @return array
     */
    public function allFiles($directory = null)
    {
        return $this->files( $directory,true );
    }

    /**
     * 获取给定目录中的所有目录。
     * @param string|null $directory
     * @param bool $recursive
     * @return array
     */
    public function directories($directory = null,$recursive = false)
    {
        return $this->filesystem->listContents($directory ?? '',$recursive )->filter( function (StorageAttributes $attributes) {
                return $attributes->isDir();
            })->map( function (StorageAttributes $attributes) {
                return $attributes->path();
            })->toArray();
    }

    /**
     *获取给定目录中的所有目录(递归)。
     * @param string|null $directory
     * @return array
     */
    public function allDirectories($directory = null)
    {
        return $this->directories( $directory,true );
    }

    /**
     * 创建一个目录。
     * @param string $path
     * @return bool
     */
    public function makeDirectory($path)
    {
        try {
            $this->filesystem->createDirectory( $path );
        } catch ( UnableToCreateDirectory|UnableToSetVisibility $e ) {
            throw_if( $this->throwsExceptions(),$e );

            return false;
        }

        return true;
    }

    /**
     * 递归删除一个目录。
     * @param string $directory
     * @return bool
     */
    public function deleteDirectory($directory)
    {
        try {
            $this->filesystem->deleteDirectory( $directory );
        } catch ( UnableToDeleteDirectory $e ) {
            throw_if( $this->throwsExceptions(),$e );
            return false;
        }
        return true;
    }

    /**
     * 魔术方法
     */
    public function __call($method,$parameters)
    {
        return $this->filesystem->$method( ...$parameters );
    }
}
