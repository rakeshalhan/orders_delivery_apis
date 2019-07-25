<?php

namespace App\Helpers;

use App\Http\Models\Distance;
use App\Library\Distance\GoogleDistanceMatrix;

class DistanceHelper
{
    /**
     * @var GoogleDistanceMatrix
     */
    protected $googleDistanceMatrix;

    /**
     * @var Distance
     */
    protected $distance;

    public function __construct(GoogleDistanceMatrix $googleDistanceMatrix)
    {
        $this->googleDistanceMatrix = $googleDistanceMatrix;
    }

    /**
     * Evaluate the distance using google map API and save the same in system for future use to avoid redundant hit of google map API for same geo coordinates
     *
     * @param array $origin
     * @param array $destination
     *
     * @return Distance|string $returnData
     */
    public function evaluateDistance($origin, $destination)
    {
        $responseData = $this->getDistance($origin, $destination);
        if (!is_int($responseData)) {
            // returning error message, based on Google Map API response
            $returnData = $responseData;
        } else {
            // if distance is being calculated as INT, saving into database
            $this->distance = new Distance;
            $this->distance->saveDistance($origin, $destination, $responseData);

            // returning Distance object
            $returnData = $this->distance;
        }

        return $returnData;
    }

    /**
     * Hitting google map API to evaluate distance between provided geo coordinates
     *
     * @param string $origin
     * @param string $destination
     *
     * @return int|string Distance in meters or error code in string
     */
    public function getDistance($origin, $destination)
    {
        return $this->googleDistanceMatrix->getDistance($origin, $destination);
    }
}
