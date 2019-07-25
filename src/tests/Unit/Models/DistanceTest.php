<?php

use App\Http\Models\Distance;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class DistanceTest extends Tests\TestCase
{

    use WithoutMiddleware;

    protected function setUp()
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();
    }

    public function testGetFirstStoredDistanceObject_NegativeTestCase_GeoCoordinatesOutOfRangeData()
    {
        echo "\n >>>>> Unit test:: Model:: Distance - Method: getFirstStoredDistanceObject - Negative test - Invalid params: Out of range geo coordinates - Expected return: null";

        // Out of range geo coordinates
        $distanceCoordinates = array(
            'origin' => array('90.123457', '75.72294'),
            'destination' => array('28.4601', '77.02635'),
        );

        $model = new \App\Http\Models\Distance();

        // must return null as google map api won't find distance for the provided geo coordinates
        $this->assertEquals(null, $model->getFirstStoredDistanceObject($distanceCoordinates['origin'], $distanceCoordinates['destination']));
    }

    public function testGetFirstStoredDistanceObject_PositiveTestCase()
    {
        echo "\n >>>>> Unit test:: Model:: Distance - Method: getFirstStoredDistanceObject - Positive test";

        // Out of range geo coordinates
        $distanceCoordinates = array(
            'origin' => array('29.15394', '75.72294'),
            'destination' => array('28.4601', '77.02635'),
        );

        // saving dummy data first
        $model = new \App\Http\Models\Distance();
        $model->saveDistance($distanceCoordinates['origin'], $distanceCoordinates['destination'], $this->faker->numberBetween(1000, 9999));

        // must return null as google map api won't find distance for the provided geo coordinates
        $this->assertInstanceOf('\App\Http\Models\Distance', $model->getFirstStoredDistanceObject($distanceCoordinates['origin'], $distanceCoordinates['destination']));
    }
}
