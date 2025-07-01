<?php

namespace App\Repositories;

use App\Models\PropertyImage;
use App\Models\User;

class ImageRepository{
    public function addPropertyImages($properyId, $imagesPaths) {
        foreach ($imagesPaths as $imagePath) {
            PropertyImage::create([
                'property_id' => $properyId,
                'image_path' => $imagePath
            ]);
        }
    }


    public function getImagesByPropertyId($properyId) {
        return PropertyImage::where('property_id', $properyId)->get();
    }
    
    public function deleteImage($imageId) {
        return PropertyImage::where('id', $imageId)->delete();
    }

    
    
}