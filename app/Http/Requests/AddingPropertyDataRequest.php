<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddingPropertyDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "owner_id" => "integer|required",
            "country_name" => "string|required",
            "city_name" => "string|required",
            "altitude" => "numeric",
            "longitude" => "numeric",
            "additional_info" => "string",
            "area" => "numeric",
            "exposure" => "array",
            "bathrooms" => "integer",
            "balconies" => "integer",
            "ownership_type" => "string",
            "property_physical_status" => "string",
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
            "property_type" => "string",
            "appartment_number" => "integer",
            "building_number" => "integer",
            "property_type" => "string",
            "bedrooms" => "integer",
            "floors" => "integer",
            "floor" => "integer",
            "building_number" => "integer",
            "appartment_number" => "integer",
        ];
    }
}
