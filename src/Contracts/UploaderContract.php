<?php
namespace SunnyShift\Uploader\Contracts;

interface UploaderContract
{
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
     * 响应的url的键
     * @return string
     */
    public function responseKey() : string;
}