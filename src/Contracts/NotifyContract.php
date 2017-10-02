<?php
namespace SunnyShift\Uploader\Contracts;

interface NotifyContract
{
    public function auth() : boolean;

    public function response() : array;
}