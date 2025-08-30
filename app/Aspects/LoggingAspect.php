<?php

namespace App\Aspects;

use Exception;
use App\Models\Log;
use Illuminate\Support\Facades\Validator;

class LoggingAspect
{   

    protected $operationsLabels = [
        'updateUserProfile' => 'Update the profile',
        'resetPassword' => 'Reset the passwrod',
        'upgradeToSeller' => 'Upgrade to seller',
        'addProperty' => 'Add property',
        'updateProperty' => 'Update property',
        'propertySold' => 'Sold property',
        'deleteProperty' => 'Delete property',
        'addPropertyToFavorite' => 'Add property to favorite',
        'removePropertyFromFavorite' => 'Remove property from favorite',

        'upgradeToServiceProvider' => 'Upgrade to service provider',
        'addService' => 'Add service',
        'removeService' => 'Remove service',
        'editService' => 'Edit service',

        'reportProperty' => 'Report property',
        'reportServiceProvider' => 'Report service provider',
    ];


    public function before($operation){
        Log::create([
            "user_id" => auth()->user() != null ? auth()->user()->id : null,
            "name" => auth()->user() != null ? auth()->user()->first_name . ' ' . auth()->user()->last_name : 'unregistered user',
            "operation" => $this->operationsLabels[$operation],
            "status" => "Started",
        ]);
    }
      

    public function after($operation){
        Log::create([
            "user_id" => auth()->user() != null ? auth()->user()->id : null,
            "name" => auth()->user() != null ? auth()->user()->first_name . ' ' . auth()->user()->last_name : 'unregistered user',
            "operation" => $this->operationsLabels[$operation],
            "status" => "Finished",
        ]);
    }

    public function exception($operation){
        Log::create([
            "user_id" => auth()->user() != null ? auth()->user()->id : null,
            "name" => auth()->user() != null ? auth()->user()->first_name . ' ' . auth()->user()->last_name : 'unregistered user',
            "operation" => $this->operationsLabels[$operation],
            "status" => "Exception",
        ]);
    }

}
