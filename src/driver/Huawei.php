<?php
declare( strict_types = 1 );

namespace apixx\filesystem\driver;
use apixx\filesystem\Driver;
use apixx\Obs\ObsAdapter;
use Obs\ObsClient;

class Huawei extends Driver
{
    protected function createAdapter()
    {
        $config            = [
            'key'      => $this->config['key'],
            'secret'   => $this->config['secret'],
            'bucket'   => $this->config['bucket'],
            'endpoint' => $this->config['endpoint'],
        ];
        $client            = new ObsClient( $config );
        $config['options'] = [
            'url'             => '',
            'endpoint'        => $this->config['endpoint'],
            'bucket_endpoint' => '',
            'temporary_url'   => '',
        ];
        return new ObsAdapter( $client,$this->config['bucket'],$this->config['prefix'],null,null,$config['options'] );
    }

}
