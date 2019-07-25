<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Order App Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to show various errors/success in the api.
    |
    */
    'REQ_ORIGIN' => "INVALID DATA",
    'REQ_DESTINATION' => "INVALID DATA",
    'ORIGIN_INVALID_PARAMETERS' => "INVALID DATA",
    'DESTINATION_INVALID_PARAMETERS' => "INVALID DATA",
    'SAME_ORIGIN_DESTINATION' => "REQUESTED_ORIGIN_AND_DESTINATION_IS_SAME",

    "GOOGLE_API" =>  ["REQUEST_DENIED" => "REQUEST_TO_GOOGLE_DISTANCE_API_IS_DENIED",
                    "OVER_QUERY_LIMIT" => "OVER_QUERY_LIMIT_FOR_GOOGLE_API_KEY",
                    "NOT_FOUND" => "GOOGLE_API_GEOCODING_FOR_ORIGIN_OR_DESTINATION_CANNOT_PAIRED",
                    "ZERO_RESULTS" => "GOOGLE_API_DOESNOT_FOUND_ANY_ROUTES_FOR_GIVEN_VALUES",
                    "NO_RESPONSE" => 'GOOGLE_DISTANCE_API_RETURNS_NULL'
    ],
    'ZERO_RESULTS' => 'GOOGLE_API_DOESNOT_FOUND_ANY_ROUTES_FOR_GIVEN_VALUES',
    'INVALID_STATUS' => "STATUS_IS_INVALID",
    'INVALID_ORDER' => "INVALID_ID",
    'ALREADY_TAKEN' => "ORDER_ALREADY_BEEN_TAKEN",

    'REQ_PAGE_PARAM' => "REQUEST_PARAMETER_MISSING",
    'INVALID_PAGE' => "INVALID_PARAMETERS",
    'REQ_LIMIT_PARAM' => "REQUEST_PARAMETER_MISSING",
    'INVALID_LIMIT' => "INVALID_PARAMETERS",
    'INVALID_PAGE_TYPE' => "INVALID_PARAMETER_TYPE",
    'INVALID_LIMIT_TYPE' => "INVALID_PARAMETER_TYPE"
];