<?php
namespace SunnyShift\Uploader;

use Illuminate\Http\Request;
use SunnyShift\Uploader\Adapter\BOS;
use SunnyShift\Uploader\Adapter\COS;
use SunnyShift\Uploader\Adapter\Local;
use SunnyShift\Uploader\Adapter\Qiniu;
use SunnyShift\Uploader\Adapter\QOS;
use SunnyShift\Uploader\Adapter\SCS;
use SunnyShift\Uploader\Adapter\Upyun;
use SunnyShift\Uploader\Adapter\OSS;
use SunnyShift\Uploader\Contracts\UploaderContract;
use Exception;

class UploaderManager
{
    private $config;

    private $app;

    private $request;

    private $adapters = [
        'public' =>  Local::class,
        'qiniu'  =>  Qiniu::class,
        'upyun'  =>  Upyun::class,
        'oss'    =>  OSS::class,
        'cos'    =>  COS::class,
        'bos'    =>  BOS::class,
        'scs'    =>  SCS::class
    ];

    public function __construct(Request $request){
        $this->config = config('filesystems');

        $this->app = app();

        $this->request = $request;
    }

    public function extend($key, callable $func){
        $driver = call_user_func($func, $this->app);

        if (!$driver instanceof UploaderContract){
            throw new Exception('The adapter must an instance of '.UploaderContract::class);
        }

        $this->adapters[$key] = $driver;
    }

    /**
     * @param $adapter
     * @throws Exception
     */
    public function setAdapter($adapter){
        if (!$this->supported($adapter)){
            throw new Exception('This adapter is not supported.');
        }

        $this->app->singleton(UploaderContract::class, $this->adapters[$adapter]);
    }

    /**
     *  获取支持的适配器
     * @param null $adapter
     * @return array|bool
     */
    public function supported($adapter = null){
        $supports = array_keys($this->adapters);

        if ($adapter == null){
            return $supports;
        }

        return in_array($adapter, $supports);
    }

    public function build($jsoned = true){

        $adapter = $this->app->make(UploaderContract::class);

        $url = $adapter->url();

        $header = $adapter->header();

        $params = $adapter->params();

        $fileName = $adapter->fileName();

        $responseKey = $adapter->responseKey();

        $res = compact('url', 'header', 'params', 'fileName', 'responseKey');

        return $jsoned ? json_encode($res) : $res;
    }

    public function register(){
        /**
         * 设置适配器
         */
        $default = $this->config['default'];
        if (!$this->supported($default)){
            $default = 'public';
        }
        $this->setAdapter($default);
    }

}