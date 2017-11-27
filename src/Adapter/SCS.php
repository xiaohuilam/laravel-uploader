<?php
/**
 * Created by PhpStorm.
 * User: sunnyshift
 * Date: 17-11-8
 * Time: 上午10:22
 */

namespace SunnyShift\Uploader\Adapter;


class SCS
{
    private $config;
    public function __construct()
    {
        $this->config = config('filesystems.disks.scs');
    }
    public function url() : string {
        return sprintf('https://%s.sinacloud.net', $this->config['bucket']);
    }
    public function header() : array {
        return [];
    }
    public function params() : array {
        $policy = [
            'expiration' => date('Y-m-d').'T'.date('H:i:s', time() + 3600).'Z',
            'conditions' => [
                'bucket'     => $this->config['bucket'],
                'acl'        => 'private',
                ['starts-with', '$key', date('Y-m-d')]
            ]
        ];
        $policy = base64_encode(json_encode($policy));
        $secretKey = $this->config['secret_key'];
        $signature = base64_encode(hash_hmac('sha1', $policy, $secretKey, true));;
        return [
            'AWSAccessKeyId'    =>  $this->config['access_key'],
            'key'               =>  date('Y-m-d').'/{s_filename}',
            'policy'            =>  $policy,
            'signature'         =>  $signature,
        ];
    }
    public function fileName() : string {
        return 'file';
    }
    public function responseKey(): string {
        return 'key';
    }

}