<?php

namespace App\Library\Distance;

class CallGoogleApi
{
    /**
     * Just to get response for GET RESTful APIs
     *
     * @param string $queryString
     *
     * @return array
     */
    public function getApiResponse($queryString)
    {
        \error_log('CallGoogleApi:: getApiResponse($queryString)');
        return \json_decode(\json_encode(\json_decode(\file_get_contents($queryString))), true);
    }
}
