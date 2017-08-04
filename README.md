
# Laravel Uploader

`Laravel`下的一个上传组件

## 安装

```sh
composer require sunnyshift/laravel-uploader
```

## 添加服务提供最

```php
SunnyShift\LaravelUploader\UploadServiceProvider::class,
```

## 生成资源文件

```sh
php artisan vendor:publish --provider=SunnyShift\\LaravelUploader\\UploadServiceProvider
```

## 使用

1. 添加上传组件到页面

    ```php
    @uploader(['name' => 'avatar', 'max' => 3, 'accept' => 'jpg,png,gif'])
    ```

2. 添加资源文件

    ```php
    @uploader('assets')
    ```
    
    ```angular2html
    该组件依赖`jQuery`，所以在引入的资源文件的时候必须先引入`jQuery`
    ```

## License

MIT

## 鸣谢
[https://github.com/overtrue/laravel-uploader]