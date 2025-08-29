<?php

namespace App\Aspects;

use Exception;
use App\Models\Log;
use Illuminate\Support\Facades\Validator;

class LoggingAspect
{   
    public function before($operation){
        Log::create([
            "user_id" => auth()->user() != null ? auth()->user()->id : null,
            "name" => auth()->user() != null ? auth()->user()->first_name + ' ' + auth()->user()->first_name : 'unregistered user',
            "operation" => $operation,
            "status" => "Started",
        ]);
    }
      

    public function after($operation){
        Log::create([
            "user_id" => auth()->user() != null ? auth()->user()->id : null,
            "name" => auth()->user() != null ? auth()->user()->username : 'unregistered user',
            "operation" => $operation,
            "status" => "Finished",
        ]);
    }

    public function exception($operation){
        Log::create([
            "user_id" => auth()->user() != null ? auth()->user()->id : null,
            "name" => auth()->user() != null ? auth()->user()->username : 'unregistered user',
            "operation" => $operation,
            "status" => "Exception",
        ]);
    }

}
