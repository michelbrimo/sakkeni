<?php

namespace App\Repositories;

use App\Models\PropertyImage;

class ImageRepository{
    public function addImages($properyId, $imagesPaths) {
        foreach ($imagesPaths as $imagePath) {
            PropertyImage::create([
                'property_id' => $properyId,
                'image_path' => $imagePath
            ]);
        }
    }

}