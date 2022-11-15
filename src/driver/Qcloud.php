<?php
declare( strict_types = 1 );

namespace apixx\filesystem\driver;
use apixx\filesystem\Driver;
use apixx\Cos\CosAdapter;

class Qcloud extends Driver
{

    protected function createAdapter()
    {
        $config = [
            'app_id'     => $this->config['appId'], 
            'secret_id'  => $this->config['secretId'],
            'secret_key' => $this->config['secretKey'],
            'region'     => $this->config['region'],
            'bucket'     => $this->config['bucket'],
            'cdn'        => $this->config['url'],
            'domain'     => $this->config['domain'], 
            'signed_url' => false,
            'use_https'  => true, 
        ];
        // code($config);
        return new CosAdapter($config);
    }
}