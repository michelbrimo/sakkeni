<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddingPropertyDataRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    

    public function rules()
    {
        return [
            "country_id" => "integer|required",
            "city_id" => "integer|required",
            "latitude" => "numeric",
            "longitude" => "numeric",
            "additional_info" => "string",
            "area" => "numeric",
            "exposure" => "array",
            "bathrooms" => "integer",
            "balconies" => "integer",
            "ownership_type" => "string",
            "physical_status_type_id" => "integer|required",
            "property_type_id" => "integer|required",
            "images" => "array",
            "amenities" => "array",
            "delivery_date" => "date",
            "first_pay" => "numeric",
            "pay_plan" => "json",
            "overall_payment" => "numeric",
            "is_furnished" => "boolean",
            "sell_type" => "string",
            "lease_period" => "string",
            "payment_plan" => "string",
            "price" => "numeric",
            "bedrooms" => "integer",
            "floors" => "integer",
            "floor" => "integer",
            "building_number" => "integer",
            "apartment_number" => "integer",
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
