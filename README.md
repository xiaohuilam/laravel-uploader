
# Laravel Uploader

`Laravel`下的一个上传组件, 支持直传到第三方云存储。

## 安装

```sh
composer require sunnyshift/laravel-uploader
```

## 添加服务提供者

```php
SunnyShift\Uploader\UploaderServiceProvider::class,
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

## 直传到云存储
该组件支持直传到第三方云存储，实际上就是模拟了表单上传的方式。从流程上来说相比于传统的先上传到服务器，再从服务器传到云存储来说，少了一步转发。从架构上来说，原来的上传都统一走网站服务器，上传量过大时，瓶颈在网站服务器，可能需要扩容网站服务器。采用表单上传后，上传都是直接从客户端发送到云存储。上传量过大时，压力都在云存储上，由云存储来保障服务质量。

目前支持的第三方云储存：
`本地(local)` `百度云(bos)` `腾讯云(cos)` `阿里云(oss)` `七牛云(qiniu)` `新浪云(scs)` `又拍云(upyun)` 
> 其中的本地不算云存储，只是标识仍旧支持本地磁盘存储。

- 青云的鉴权方式比较奇特所以暂时无法支持
- UCloud的鉴权方式太复杂暂时不打算支持

### 1.配置
百度云：
```php
'disks' => [
    'bos' => [
        'driver'       => 'bos',
        'access_key_id'    =>  'xxxxxxxxxx',
        'access_key_secret'   => 'xxxxxxxxxx',
        'bucket'       => 'xxx',
        'region'    =>  'gz'    //改成存储桶相应地域
    ],
]
```

腾讯云：
```php
'cos' => [
        'driver'       => 'cos',
        'app_id'    =>  '123456789',
        'secret_id'   => 'xxxxxxxxxxx',
        'secret_key'   => 'xxxxxxxxxxx',
        'bucket'       => 'xxxxxxxxx',
        'region'    =>  'sh'    //改成存储桶相应地域
    ]
```
> 注意，腾讯云存储的时候不是以资源的访问路径存的，会加上appid和存储桶的参数。主要是腾讯云上传后没有返回资源的相对路径，而且这样的存储方式也是官方推崇的。

阿里云：
```php
'oss' => [
        'driver'       => 'oss',
        'access_key'   => 'xxxxxxxxxx',
        'secret_key'   => 'xxxxxxxxxx',
        'bucket'       => 'xxxxx',
    ],
```

七牛云：
```php
'qiniu' => [
        'driver'     => 'qiniu',
        'access_key' => 'xxxxxxxxxxxxxxxxxx',
        'secret_key' => 'xxxxxxxxxxxxxxxxxx',
        'bucket'     => 'xxxxxxxxxxxxxxxxxx',
        'domain'     => 'xxxxxxxxxxx'
    ],
```

新浪云：
```php
'scs' => [
        'driver'       => 'scs',
        'access_key'    =>  'xxxxxx',
        'secret_key'   => 'xxxxxxx',
        'bucket'       => 'xxxxxxxx'
    ]
```

又拍云：
```php
'upyun' => [
        'driver'     => 'upyun',
        'operator'   => 'xxxxx',
        'password'   => 'xxxxxx',
        'bucket'     => 'xxxxxx',
        'domain'     => 'xxxxxx',
        'form_api_secret'     =>  'xxxxx',
    ]
```

### 2.设置云储存
```php
FILESYSTEM_DRIVER=qiniu
```

## 扩展
当然，你也可以拓展支持的云存储，很简单:

### 1.新增驱动
```php
<?php
use SunnyShift\Uploader\Contracts\UploaderContract;

class MyStorage implements UploaderContract {
        /**
     * 请求url
     * @return string
     */
    public function url() : string;

    /**
     * 请求头
     * @return array
     */
    public function header() : array;

    /**
     * 请求体
     * @return array
     */
    public function params() : array;

    /**
     * 文件表单名
     * @return string
     */
    public function fileName() : string;

    /**
     * 响应的url的键.
     * @return string
     */
    public function responseKey() : string;
}
```

### 2.拓展
```php
Uploader::extend('mystorage', function($app) {
    return new MyStorage();
});
```

### 3.使用
```php
FILESYSTEM_DRIVER=mystorage
```
或者
```php
Uploader::setAdapter('mystorage');
```

## 说明
自带的上传组件UI是按照WEUI的上传功能设计的，可能不能满足您业务的需求，那么您可以自行构建UI后再把一些请求参数传到前台。也很简单：
```php
$data = Uploader::build($jsoned = true);
```
其中`data`包含实现`SunnyShift\Uploader\Contracts\UploaderContract`的所有参数，可以根据具体业务使用。

## License
MIT

