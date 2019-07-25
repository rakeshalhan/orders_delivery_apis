<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Distance extends Model
{
    protected $table = 'distances';

    /**
     * Find the first saved object stored in database for provided origin and destination
     *
     * @param  array $origin
     * @param  array $destination
     *
     * @return self|null
     */
    public function getFirstStoredDistanceObject($origin, $destination)
    {
        return self::where(array(
            array('start_lat', '=', $origin[0]),
            array('start_long', '=', $origin[1]),
            array('end_lat', '=', $destination[0]),
            array('end_long', '=', $destination[1]),
        ))->first();
    }

    /**
     * Saving the distance model
     *
     * @param array $origin
     * @param array $destination
     * @param int $totalDistance
     *
     * @return \App\Http\Models\Distance
     */
    public function saveDistance($origin, $destination, $totalDistance)
    {
        $this->start_lat = $origin[0];
        $this->start_long = $origin[1];
        $this->end_lat = $destination[0];
        $this->end_long = $destination[1];
        $this->total_distance = $totalDistance;
        $this->save();
    }
}
