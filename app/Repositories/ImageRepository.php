<?php

namespace App\Repositories;

use App\Models\PropertyImage;
use App\Models\User;
use App\Models\WorkGallery;

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
    
    public function deletePropertyImage($imageId) {
        return PropertyImage::where('id', $imageId)->delete();
    }

    public function addServiceImages($serviceId, $imagesPaths) {
        foreach ($imagesPaths as $imagePath) {
            WorkGallery::create([
                'service_provider_service_id' => $serviceId,
                'image_path' => $imagePath
            ]);
        }
    }

    public function getImagesByServiceId($serviceId) {
        return WorkGallery::where('service_provider_service_id', $serviceId)->get();
    }
    
    public function deleteServiceImage($imageId) {
        return WorkGallery::where('id', $imageId)->delete();
    }


    
    
}