<?php
namespace SunnyShift\LaravelUploader;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use SunnyShift\LaravelUploader\Services\FileUpload;

/**
 * Created by PhpStorm.
 * User: sunnyshift
 * Date: 17-8-3
 * Time: 下午2:06
 */
class UploaderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoute();
        $this->loadViews();
        $this->loadAssets();
        $this->registerServices();
        $this->registerDirective();
    }

    protected function loadRoute(){
        if (! $this->app->routesAreCached()){
            $this->app->make('router')->post('sunnyshift/upload', __NAMESPACE__.'\Http\Controllers\UploaderController@upload')->name('sunnyshift.upload');
        }
    }

    protected function loadViews()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'uploader');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/uploader'),
        ]);
    }

    protected function registerServices()
    {
        $this->app->singleton(FileUpload::class, function ($app) {
            return new FileUpload($app['filesystem']);
        });
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