<?php
namespace SunnyShift\Uploader;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use SunnyShift\Uploader\Adapter\OSS;
use SunnyShift\Uploader\Adapter\Upyun;
use SunnyShift\Uploader\Services\FileUpload;
use SunnyShift\Uploader\Adapter\Local;
use SunnyShift\Uploader\Adapter\Qiniu;

class UploaderServiceProvider extends ServiceProvider
{
    private $adapters = [
        'public' =>  Local::class,
        'qiniu'  =>  Qiniu::class,
        'upyun'  =>  Upyun::class,
        'oss'    =>  OSS::class
    ];

    public function boot()
    {
        $this->loadRoute();
        $this->loadViews();
        $this->loadAssets();
        $this->registerDirective();

        View::share('uploader_options', Uploader::build());
    }

    public function register(){
        $this->app->singleton(FileUpload::class, function ($app) {
            return new FileUpload($app['filesystem']);
        });

        $this->app->singleton(UploaderManager::class, function (){
            return new UploaderManager();
        });

        foreach ($this->adapters as $key => $adapter){
            Uploader::extend($key, function () use ($adapter){
                return $this->app->make($adapter);
            });
        }
    }

    protected function loadRoute(){
        if (! $this->app->routesAreCached()){
            $this->app->make('router')->post('sunnyshift/upload', __NAMESPACE__.'\Http\Controllers\UploaderController@upload');
            $this->app->make('router')->post('sunnyshift/notify', __NAMESPACE__.'\Http\Controllers\NotifyController@notify');
        }
    }

    protected function loadViews()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'uploader');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/uploader'),
        ]);
    }

    protected function loadAssets()
    {
        $this->publishes([
            __DIR__.'/../resources/public' => public_path('vendor/uploader'),
        ], 'public');
    }

    protected function registerDirective(){
        Blade::directive('uploader', function($expression) {
            if (str_contains($expression, ',')){
                $parts = explode(',', trim($expression, '()'));
                $data = count($parts) > 1 ? implode(',', $parts) : '[]';
                return "<?php echo \$__env->make('uploader::uploader', (array)$data)->render(); ?>";
            }else{
                return "<?php echo \$__env->make('uploader::assets')->render(); ?>";
            }
        });
    }

}