<?php
namespace SunnyShift\Uploader\Notify;

use Illuminate\Http\Request;
use SunnyShift\Uploader\Contracts\NotifyContract;

class OSS implements NotifyContract
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function auth(): boolean
    {
        $authorizationBase64 = $this->request->header('authorization');

        $pubKeyUrlBase64 = $this->request->header('x-oss-pub-key-url');

        if (!starts_with($pubKeyUrlBase64, 'http://gosspublic.alicdn.com/') || !starts_with($pubKeyUrlBase64, 'https://gosspublic.alicdn.com/')){
            return false;
        }


        if ($authorizationBase64 == '' || $pubKeyUrlBase64 == '') {
            return false;
        }

        $signature = base64_decode($authorizationBase64);

        $pubKey = $this->getPublicKey($pubKeyUrlBase64);

        if (!$pubKey){
            return false;
        }

        // 4.获取回调body
        $body = file_get_contents('php://input');

        // 5.拼接待签名字符串
        $path = $this->request->getRequestUri();

        $pos = strpos($path, '?');

        if ($pos === false) {
            $signStr = urldecode($path)."\n".$body;
        } else {
            $signStr = urldecode(substr($path, 0, $pos)).substr($path, $pos, strlen($path) - $pos)."\n".$body;
        }

        return openssl_verify($signStr, $signature, $pubKey, OPENSSL_ALGO_MD5) == 1;
    }

    public function response(): array
    {
        return [
            'Status'    =>  'Ok',
            'key'       =>  $this->request->input('object')
        ];
    }


    private function getPublicKey($pubKeyUrlBase64){
        $pubKeyUrl = base64_decode($pubKeyUrlBase64);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $pubKeyUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        return curl_exec($ch);
    }

}