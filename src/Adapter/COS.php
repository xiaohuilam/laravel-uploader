<?php
namespace SunnyShift\Uploader\Adapter;
use Qiniu\Auth;
use Qiniu\Zone;
use SunnyShift\Uploader\Contracts\UploaderContract;

class COS implements UploaderContract
{
    private $config;

    public function __construct()
    {
        $this->config = config('filesystems.disks.cos');
    }

    public function url() : string {
        $uri = 'https://%s.file.myqcloud.com/files/v2/%s/%s/%s';

        return sprintf($uri, $this->config['region'], $this->config['app_id'], $this->config['bucket'], str_random());
    }

    public function header() : array {
        $appid = $this->config['app_id'];
        $bucket = $this->config['bucket'];
        $secret_id = $this->config['secret_id'];
        $secret_key = $this->config['secret_key'];
        $expired = time() + 60;

        $current = time();
        $rdm = rand();

        $multi_effect_signature = 'a='.$appid.'&b='.$bucket.'&k='.$secret_id.'&e='.$expired.'&t='.$current.'&r='.$rdm.'&f=';
        $multi_effect_signature = base64_encode(hash_hmac('SHA1', $multi_effect_signature, $secret_key, true).$multi_effect_signature);
        return [
            'Authorization' =>  $multi_effect_signature
        ];
    }

    public function params() : array {
        return [
            'op'    =>  'upload'
        ];
    }

    public function fileName() : string {
        return 'fileContent';
    }

    public function responseKey(): string {
        return 'data.resource_path';
    }

}