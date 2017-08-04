<?php
if (!function_exists('upload_url')) {
    /**
     * Get uploader url
     *
     * @return string
     */
    function upload_url()
    {
        if (is_qiniu()){
            $zone = new \Qiniu\Zone('https');
            $qiniu = config('filesystems.disks.qiniu');
            $url = $zone->getUpHosts($qiniu['access_key'], $qiniu['bucket']);
            return array_get($url, '0.1');
        }else{
            return route('sunnyshift.upload');
        }
    }
}

if (!function_exists('is_qiniu')) {
    /**
     * disk is qiniu
     *
     * @return boolean
     */
    function is_qiniu()
    {
        return config('filesystems.default') === 'qiniu';
    }
}

if (!function_exists('qiniu_token')){
    function qiniu_token(){
        $qiniu = config('filesystems.disks.qiniu');

        $auth = new \Qiniu\Auth($qiniu['access_key'], $qiniu['secret_key']);

        return $auth->uploadToken($qiniu['bucket']);
    }
}