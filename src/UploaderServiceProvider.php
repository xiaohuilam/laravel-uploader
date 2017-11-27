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
use Illuminate\Support\Facades\Route;

class UploaderServiceProvider extends ServiceProvider
{

    public function boot()
    {
        Uploader::register();

        View::share('uploader_options', Uploader::build());
    }

    public function register(){
        $this->loadRoute();
        $this->loadViews();
        $this->loadAssets();
        $this->registerDirective();

        $this->app->singleton(FileUpload::class, function ($app) {
            return new FileUpload($app['filesystem']);
        });

        $this->app->singleton(UploaderManager::class, function ($app){
            return new UploaderManager($app->request);
        });
    }

    protected function loadRoute(){
        if (! $this->app->routesAreCached()){
            Route::post('sunnyshift/upload', __NAMESPACE__.'\Http\Controllers\UploaderController@upload')->name('sunnyshift.upload');
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