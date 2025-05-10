<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FilterPropertiesRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            "country_id" => "integer",
            "city_id" => "integer",
            "min_area" => "numeric",
            "max_area" => "numeric",
            "bathrooms" => "integer",
            "balconies" => "integer",
            "amenity_ids" => "array",
            "is_furnished" => "boolean",
            "min_price" => "numeric",
            "max_price" => "numeric",
            "lease_period" => 'string',
            "min_first_pay" => "numeric",
            "max_first_pay" => "numeric",
            "delivery_date" => "date",
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation Error',
            'errors' => $validator->errors(),
        ], 422));
    }
}
