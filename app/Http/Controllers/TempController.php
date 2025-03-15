<?php

namespace App\Http\Controllers;

use App\Services\ServiceTransfromer;
use Illuminate\Http\Request;

class TempController extends Controller
{
    protected $service_transformer;
    
    function __construct(){
        $this->service_transformer = new ServiceTransfromer();
    }
}
