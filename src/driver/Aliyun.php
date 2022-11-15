<?php
declare( strict_types = 1 );

namespace apixx\filesystem\driver;
use apixx\filesystem\Driver;
use apixx\Oss\OssAdapter;

class Aliyun extends Driver
{

    protected function createAdapter()
    {
        return new OssAdapter($this->config['accessId'],$this->config['accessSecret'],$this->config['endpoint'],$this->config['bucket']);
    }
}