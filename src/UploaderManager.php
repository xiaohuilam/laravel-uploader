<?php
namespace SunnyShift\Uploader;

use Illuminate\Http\Request;
use SunnyShift\Uploader\Adapter\Local;
use SunnyShift\Uploader\Adapter\Qiniu;
use SunnyShift\Uploader\Adapter\Upyun;
use SunnyShift\Uploader\Adapter\OSS;
use SunnyShift\Uploader\Contracts\NotifyContract;
use SunnyShift\Uploader\Notify\OSS as OSSNotifier;
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
        'oss'    =>  OSS::class
    ];

    private $notifies = [
        'oss'   =>  OSSNotifier::class
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

    public function notifier($key, callable $func){
        $driver = call_user_func($func, $this->app);

        if (!$driver instanceof NotifyContract){
            throw new Exception('The adapter must an instance of '.NotifyContract::class);
        }

        $this->notifies[$key] = $driver;
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

    public function build(){

        $adapter = $this->app->make(UploaderContract::class);

        $url = $adapter->url();

        $header = $adapter->header();

        $params = $adapter->params();

        $fileName = $adapter->fileName();

        $responseKey = $adapter->responseKey();

        return json_encode(compact('url', 'header', 'params', 'fileName', 'responseKey'));
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

        /**
         * 设置通知者
         */
        if ($this->request->has('notifier')){
            $notifier = $this->request->input('notifier');

            if (in_array($notifier,  array_keys($this->notifies))){
                $this->app->singleton(NotifyContract::class, $this->notifies[$notifier]);
            }
        }
    }

}