<?php

namespace App\Library\Distance;

use App\Library\Distance\CallGoogleApi;

class GoogleDistanceMatrix
{
    /**
     * CallGoogleApi object
     *
     * @var CallGoogleApi
     */
    protected $callingGoogleApiObject;

    public function __construct(CallGoogleApi $callingGoogleApiObject)
    {
        $this->callingGoogleApiObject = $callingGoogleApiObject;
    }

    /**
     * Returns distance between Origin and Destination in meters
     * In case of any error send error code in string format
     *
     * @param array $origin
     * @param array $destination
     *
     * @return int|string
     */
    public function getDistance($origin, $destination)
    {
        $returnData = 'GOOGLE_API.NO_RESPONSE';

        try {
            $googleApiKey = \env('MAP_API_KEY');
            $queryString = \env('MAP_API_URL') . '?units=imperial&origins=' . \implode(',', $origin) . '&destinations=' . \implode(',', $destination) . '&key=' . $googleApiKey;

            $responseDataArray = $this->callingGoogleApiObject->getApiResponse($queryString);

            if (!empty($responseDataArray) && isset($responseDataArray['status'])) {
                if ('OK' == \trim($responseDataArray['status'])) {
                    $dataElements = $responseDataArray['rows'][0]['elements'][0];
                    if (isset($dataElements['distance']['value'])) {
                        $returnData = (int) $dataElements['distance']['value'];
                    }
                } else {
                    $returnData = 'GOOGLE_API.' . $responseDataArray['status'];
                }
            }
        } catch (\Exception $ex) {
            $returnData = 'GOOGLE_API.NO_RESPONSE';
        }

        return $returnData;
    }
}
