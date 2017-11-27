<?php
namespace SunnyShift\Uploader\Adapter;
use Qiniu\Auth;
use Qiniu\Zone;
use SunnyShift\Uploader\Contracts\UploaderContract;

class Qiniu implements UploaderContract
{
    private $config;

    public function __construct()
    {
        $this->config = config('filesystems.disks.qiniu');
    }

    public function url() : string {
        $zone = Zone::queryZone($this->config['access_key'],$this->config['bucket']);

        return 'https://'.array_get($zone->cdnUpHosts, '0');
    }

    public function header() : array {
        return [];
    }

    public function params() : array {
        $auth = new Auth($this->config['access_key'], $this->config['secret_key']);
        $token = $auth->uploadToken($this->config['bucket']);

        $key = date('Y-m-d') .'/{s_filename}';

        return compact('token', 'key');
    }

    public function fileName() : string {
        return 'file';
    }

    public function responseKey(): string {
        return 'key';
    }

}