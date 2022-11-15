## Think-Filesystem-cloud

### 要求
   - php 7.1
   - topthink/framework 6.0.0
   
### 使用
```php
composer require apixx/think-filesystem
```   

### 配置
config/filesystem.php
```php
"disks" => [
   //阿里云
        'aliyun' => [
            'type'         => 'aliyun',
            'accessId'     => '',
            'accessSecret' => '',
            'bucket'       => '',
            'endpoint'     => '',
            'url'          => '',//不要斜杠结尾，此处为URL地址域名。
        ],

        //七牛
        'qiniu'  => [
            'type'      => 'qiniu',
            'accessKey' => '',
            'secretKey' => '',
            'bucket'    => '',
            'url'       => '',//不要斜杠结尾，此处为URL地址域名。
        ],

        //腾讯云
        'qcloud' => [
            'type'      => 'qcloud',
            'region'    => '', //bucket 所属区域 英文
            'appId'     => '', //域名中数字部分
            'secretId'  => '',
            'secretKey' => '',
            'bucket'    => '',
            'domain'    => '', //域名,不要增加http协议
            'url'       => '',  //CDN加速域名
        ]
   ]
```

### 感谢
   - [iiDestiny/flysystem-oss](https://github.com/iiDestiny/flysystem-oss)
   - [overtrue/flysystem-qiniu](https://github.com/overtrue/flysystem-qiniu)
   - [overtrue/flysystem-cos](https://github.com/overtrue/flysystem-cos)
   - [yzh52521/think-filesystem](https://github.com/yzh52521/think-filesystem)
   - [yzh52521/filesystem-oss](https://github.com/yzh52521/filesystem-oss)
   - [yzh52521/filesystem-obs](https://github.com/yzh52521/filesystem-obs)
### 协议
 MIT
