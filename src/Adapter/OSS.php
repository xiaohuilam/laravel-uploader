<?php
namespace SunnyShift\Uploader\Adapter;
use Qiniu\Auth;
use Qiniu\Zone;
use SunnyShift\Uploader\Contracts\UploaderContract;

class OSS implements UploaderContract
{
    private $config;

    public function __construct()
    {
        $this->config = config('filesystems.disks.oss');
    }

    public function url(): string
    {
        return sprintf('https://%s.oss-cn-hangzhou.aliyuncs.com', $this->config['bucket']);
    }

    public function header(): array
    {
        return [];
    }

    public function params(): array
    {
        $policy = [
            'expiration' => date('Y-m-d').'T'.date('H:i:s', time() + 3600).'Z',
            'conditions' => [
                ['starts-with', '$key', date('Y-m-d')]
            ]
        ];

        $policy = base64_encode(json_encode($policy));

        $secretKey = $this->config['secret_key'];

        $signature = base64_encode(hash_hmac('sha1', $policy, $secretKey, true));;

        return [
            'OSSAccessKeyId'    =>  $this->config['access_key'],
            'key'               =>  date('Y-m-d').'/{s_filename}',
            'policy'            =>  $policy,
            'signature'         =>  $signature,
        ];
    }

    public function fileName(): string
    {
        return 'file';
    }

    public function responseKey(): string
    {
        return 'Location';
    }

}