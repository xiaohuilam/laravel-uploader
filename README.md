
# Laravel Uploader

`Laravel`下的一个上传组件

## 安装

```sh
composer require sunnyshift/laravel-uploader
```

## 添加服务提供者

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
    
    > 该组件依赖`jQuery`，所以在引入的资源文件的时候必须先引入`jQuery`

## 直传七牛
支持直传到七牛云，在`filesystems`新增一块磁盘：
```php
'disks' => [
    'qiniu' => [
        'driver'     => 'qiniu',
        'access_key' => env('QINIU_ACCESS_KEY'),
        'secret_key' => env('QINIU_SECRET_KEY'),
        'bucket'     => env('QINIU_BUCKET'),
        'domain'     => env('QINIU_DOMAIN'), // or host: https://xxxx.clouddn.com
    ],
]
```

修改配置，设置默认磁盘：
```php
FILESYSTEM_DRIVER=qiniu
```

## License

MIT

## 鸣谢
https://github.com/overtrue/laravel-uploader

## 关于
其实安正超已经出过一个类似的包了，为何还要再造轮子呢？因为在使用他那个包的过程发现了一系列的问题，比如文件进度不准确，错误未处理，文件数量限制无效。虽然不影响使用，但总是觉得不舒服，所以参考他的实现，自己重新制造了轮子。