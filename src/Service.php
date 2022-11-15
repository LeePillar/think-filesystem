<?php
namespace apixx\filesystem;

class Service extends \think\Service
{
    public function register()
    {
        $this->app->bind('Filesystem', Filesystem::class);
    }
}