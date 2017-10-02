<?php
namespace SunnyShift\Uploader;

use SunnyShift\Uploader\Contracts\UploaderContract;
use Exception;

class UploaderManager
{
    private $config;

    private $app;

    private $supports = [];

    const ADAPTER_PREFIX = 'adapter.';

    /**
     * @var UploaderContract
     */
    private $adapter = null;

    public function __construct(){
        $this->config = config('filesystems');

        $this->app = app();
    }

    public function extend($key, callable $func){
        $driver = call_user_func($func, $this->app);

        if (!$driver instanceof UploaderContract){
            throw new Exception('The adapter must an instance of '.UploaderContract::class);
        }

        if (!in_array($key, $this->supports)){
            array_push($this->supports, $key);
        }

        $this->app->instance(self::ADAPTER_PREFIX.$key, $driver);
    }

    /**
     * 设置适配器
     * @param $adapter
     */
    public function setAdapter($adapter){
        if (!$adapter instanceof UploaderContract){
            $adapter = $this->app->make(self::ADAPTER_PREFIX.$adapter);
        }

        $this->adapter = $adapter;
    }

    /**
     * 获取上传适配器
     * @return UploaderContract
     */
    public function getAdapter(){
        return $this->adapter;
    }

    public function build(){
        if ($this->adapter === null || !$this->adapter instanceof UploaderContract){
            $default = $this->config['default'];

            if (!in_array($default, $this->supports)){
                $default = 'public';
            }

            $this->setAdapter($default);
        }

        $url = $this->adapter->url();

        $header = $this->adapter->header();

        $params = $this->adapter->params();

        $fileName = $this->adapter->fileName();

        $responseKey = $this->adapter->responseKey();

        return json_encode(compact('url', 'header', 'params', 'fileName', 'responseKey'));
    }

}