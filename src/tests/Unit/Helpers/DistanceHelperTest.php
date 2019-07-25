<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;

class DistanceHelperTest extends Tests\TestCase
{
    use WithoutMiddleware;

    protected $googleDistanceMatrixMock;

    protected function setUp()
    {
        parent::setUp();
        $this->faker = Faker\Factory::create();

        $this->googleDistanceMatrixMock = \Mockery::mock(\App\Library\Distance\GoogleDistanceMatrix::class);

        $this->app->instance(\App\Helpers\DistanceHelper::class,
            new \App\Helpers\DistanceHelper(
                $this->googleDistanceMatrixMock
            )
        );
    }

    public function testGetDistance_PositiveTest()
    {
        echo "\n >>>>> Unit test:: Helper:: DistanceHelper - Method: getDistance - Positive test";

        $origin = array('29.15394', '75.72294');
        $destination = array('28.4601', '77.02635');

        $this->googleDistanceMatrixMock
            ->shouldReceive('getDistance')
            ->with($origin, $destination)
            ->andReturn($this->faker->numberBetween(100, 9999));

        $distanceHelper = new \App\Helpers\DistanceHelper($this->googleDistanceMatrixMock);

        $this->assertInternalType('int', $distanceHelper->getDistance($origin, $destination));
    }

    public function testGetDistance_NegativeTest()
    {
        echo "\n >>>>> Unit test:: Helper:: DistanceHelper - Method: getDistance - Negative test - error code from google map API";

        $origin = array('100.111111', '44.222222');
        $destination = array('28.4601', '77.02635');

        $this->googleDistanceMatrixMock
            ->shouldReceive('getDistance')
            ->with($origin, $destination)
            ->andReturn('GOOGLE_API.NO_RESPONSE');

        $distanceHelper = new \App\Helpers\DistanceHelper($this->googleDistanceMatrixMock);

        $this->assertInternalType('string', $distanceHelper->getDistance($origin, $destination));
    }
}
