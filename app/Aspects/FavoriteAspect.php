<?php

namespace App\Aspects;

use App\Repositories\PropertyRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class FavoriteAspect
{
    protected $property_repository;

     function __construct(){
        $this->property_repository = new PropertyRepository();
    }    
    
    public function before($functionName, $data){
        switch ($functionName){
            case 'removePropertyFromFavorite':
                $favorite = $this->property_repository->getPropertyFavorite($data['property_id'], auth()->user()->id);
                if(!$favorite)
                    throw new Exception('no favorite property was found');
                break;

            case 'addPropertyToFavorite':
                $favorite = $this->property_repository->getPropertyFavorite($data['property_id'], auth()->user()->id);
                if($favorite)
                    throw new Exception('you already have it favorited');
                break;
        }
    }
    
    public function after(){
    }

    public function exception(){
    }

}