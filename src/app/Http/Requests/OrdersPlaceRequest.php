<?php

namespace App\Http\Requests;

use App\Rules\ValidateGeoCoordinatesRule;

class OrdersPlaceRequest extends CommonFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array(
            'origin' => array(
                'required',
                'array',
                new ValidateGeoCoordinatesRule,
            ),
            'destination' => array(
                'required',
                'array',
                new ValidateGeoCoordinatesRule,
            ),
        );
    }

    /**
     * Custom message for validation
     *
     * @return array
     */
    public function messages()
    {
        return array(
            'origin.required' => 'REQ_ORIGIN',
            'destination.required' => 'REQ_DESTINATION',
        );
    }
}
