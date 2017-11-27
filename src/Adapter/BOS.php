<?php
namespace SunnyShift\Uploader\Adapter;
use Qiniu\Auth;
use Qiniu\Zone;
use SunnyShift\Uploader\Contracts\UploaderContract;

class BOS implements UploaderContract
{
    private $config;

    public function __construct()
    {
        $this->config = config('filesystems.disks.bos');
    }

    public function url() : string {
        $uri = 'http://%s.%s.bcebos.com';
        return sprintf($uri, $this->config['bucket'], $this->config['region']);

    }

    public function header() : array {
       return [];
    }

    public function params() : array {
        $policy = [
            'expiration'    =>  date('Y-m-d').'T'.date('H:i:s').'Z',
            'conditions'    =>  [
                ['bucket'    =>  $this->config['bucket']]
            ]
        ];

        $signature = hash_hmac('SHA256', base64_encode(json_encode($policy)), $this->config['access_key_secret']);

        return [
            'accessKey' =>  $this->config['access_key_id'],
            'policy'    =>  base64_encode(json_encode($policy)),
            'signature'  => $signature,
            'key'    =>  date('Y-m-d').'/{s_filename}'
        ];
    }

    public function fileName() : string {
        return 'file';
    }

    public function responseKey(): string {
        return 'key';
    }

}