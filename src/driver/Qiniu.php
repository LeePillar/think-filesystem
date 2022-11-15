<?php
declare( strict_types = 1 );

namespace apixx\filesystem\driver;
use apixx\filesystem\Driver;
use apixx\Qiniu\QiniuAdapter;

class Qiniu extends Driver
{

    protected function createAdapter()
    {
        return new QiniuAdapter(
            $this->config['accessKey'],
            $this->config['secretKey'],
            $this->config['bucket'],
            $this->config['url']
        );
    }
}