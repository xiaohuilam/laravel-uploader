<?php
namespace SunnyShift\Uploader\Http\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\App;
use SunnyShift\Uploader\Contracts\NotifyContract;

class NotifyController
{
    public function notify(){
        /**
         * @var NotifyContract
         */
        $notify = null;

        try{
            $notify = App::make(NotifyContract::class);
        }catch (BindingResolutionException $e){
            return $this->error($e->getMessage(), 400);
        }

        if (!$notify->auth()){
            return $this->error('The request authentication failed.', 403);
        }

        return response()->json($notify->response());
    }

    public function error($message, $code){
        return response()->json([
            'message'   =>  $message
        ], $code);
    }
}