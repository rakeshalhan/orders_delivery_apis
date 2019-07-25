<?php

namespace App\Http\Requests;

class OrdersListRequest extends CommonFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array(
            'page' => array(
                'required',
                'int',
                'min:1',
            ),
            'limit' => array(
                'required',
                'int',
                'min:1',
            )
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
            'page.required' => 'REQ_PAGE_PARAM',
            'page.integer' => 'INVALID_PAGE_TYPE',
            'page.min' => 'INVALID_PAGE',
            'limit.required' => 'REQ_LIMIT_PARAM',
            'limit.integer' => 'INVALID_LIMIT_TYPE',
            'limit.min' => 'INVALID_LIMIT',
        );
    }
}
