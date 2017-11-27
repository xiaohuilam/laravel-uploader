<?php
namespace SunnyShift\Uploader\Adapter;
use SunnyShift\Uploader\Contracts\UploaderContract;

class Upyun implements UploaderContract
{
    private $config;

    public function __construct()
    {
        $this->config = config('filesystems.disks.upyun');
    }

    public function url() : string {
        $domain = trim($this->config['domain'], '/');

        return $domain . '/' . $this->config['bucket'];
    }

    public function header() : array {
        return [];
    }

    public function params() : array {
        $params = [
            'bucket' => $this->config['bucket'],
            'save-key' => date('Y-m-d').'/{random32}{.suffix}',
            'expiration' => time() + 3600
        ];

        $policy = base64_encode(json_encode($params));

        $signature = md5($policy.'&'.$this->config['form_api_secret']);

        return array_merge($params,compact('policy', 'signature'));
    }

    public function fileName() : string {
        return 'file';
    }

    public function responseKey() : string {
        return 'url';
    }

}