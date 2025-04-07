<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ImageServices
{
    function _storeImages(array $images, $category, $id) {
        $i = 0;
        $imagesPaths = [];

        foreach ($images as $image) {
            $imagesPaths[$i++] = 'storage/' . Storage::disk('public')->put("$category/$id", $image);
        }

        return $imagesPaths;
    }
}
