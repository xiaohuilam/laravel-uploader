<?php
namespace SunnyShift\Uploader;

use Illuminate\Support\Facades\Facade;

class Uploader extends Facade
{
    protected static function getFacadeAccessor()
    {
        return UploaderManager::class;
    }

}