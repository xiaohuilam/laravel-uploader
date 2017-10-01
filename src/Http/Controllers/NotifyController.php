<?php
namespace SunnyShift\Uploader\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\App;
use SunnyShift\Uploader\Contracts\NotifyContract;

class NotifyController
{
    public function notify(Request $request){
        if ($request->has('adapter')){
            return $this->error('Unknown adapter.', 400);
        }

        $notifier = $request->get('notifier');

        /**
         * @var NotifyContract
         */
        $notify = null;

        try{
            $notify = App::make('notifier.'.$notifier);
        }catch (Exception $e){
            return $this->error('Un supported notifier', 400);
        }

        if (!$notify instanceof NotifyContract){
            $this->error('Notifier must be an instance of '.NotifyContract::class, 400);
        }

        if (!$notify->auth()){
            $this->error('The request authentication failed.', 4.3);
        }

        return response()->json($notify->response());
    }

    public function error($message, $code){
        return response()->json([
            'message'   =>  $message
        ], $code);
    }
}