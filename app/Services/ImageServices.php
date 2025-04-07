<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ImageServices
{
    function _saveImages(Array $images, $category, $id) {
        foreach ($images as $image) {
            Storage::disk('local')->put("$category/$id", $image);
        }
    }
}
