<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;

class OrderServiceTest extends Tests\TestCase
{
    use WithoutMiddleware;

    protected static $allowedOrderStatus = array(
        \App\Http\Models\Order::UNASSIGNED_ORDER_STATUS,
        \App\Http\Models\Order::ASSIGNED_ORDER_STATUS,
    );

    protected $distanceHelperMock;
    protected $orderModelMock;

    protected function setUp()
    {
        parent::setUp();
        $this->faker = Faker\Factory::create();

        $this->distanceHelperMock = \Mockery::mock(\App\Helpers\DistanceHelper::class);
        $this->distanceModelMock = \Mockery::mock(\App\Http\Models\Distance::class);
        $this->orderModelMock = \Mockery::mock(\App\Http\Models\Order::class);

        $this->app->instance(\App\Http\Services\OrderService::class,
            new \App\Http\Services\OrderService(
                $this->distanceHelperMock
            )
        );
    }

    /**
     * @param int|null $id
     *
     * @return \App\Http\Models\Order
     */
    private function generateFakeOrder($id = null)
    {
        $id = $id ? $id : $this->faker->randomDigit();

        $order = new \App\Http\Models\Order();
        $order->id = $id;
        $order->status = $this->faker->randomElement(self::$allowedOrderStatus);
        $order->distance_id = $this->faker->randomDigit();
        $order->total_distance = $this->faker->numberBetween(1000, 9999);
        $order->created_at = $this->faker->dateTimeBetween();
        $order->updated_at = $this->faker->dateTimeBetween();

        return $order;
    }

    public function testPlaceNewOrder_PositiveTest()
    {
        echo "\n >>>>> Unit test:: Service:: OrderService - Method: placeNewOrder - Positive test";

        $origin = array('29.15394', '75.72294');
        $destination = array('28.4601', '77.02635');

        $order = $this->generateFakeOrder();
        $this->orderModelMock
            ->shouldReceive('saveOrder')
            ->with(1, 46732)
            ->andReturn($order);

        $orderService = new \App\Http\Services\OrderService($this->distanceHelperMock);

        $this->assertInstanceOf('\App\Http\Models\Order', $orderService->placeNewOrder($origin, $destination));
    }

    public function testPlaceNewOrder_NegativeTest()
    {
        echo "\n >>>>> Unit test:: Service:: OrderService - Method: placeNewOrder - Negative test - No response from google map API";

        $origin = array('100.111111', '44.222222');
        $destination = array('28.4601', '77.02635');

        $this->distanceModelMock
            ->shouldReceive('getFirstStoredDistanceObject')
            ->with($origin, $destination)
            ->andReturn(null);

        $this->distanceHelperMock
            ->shouldReceive('evaluateDistance')
            ->with($origin, $destination)
            ->andReturn('GOOGLE_API.NO_RESPONSE');

        $orderService = new \App\Http\Services\OrderService($this->distanceHelperMock);

        $this->assertEquals(false, $orderService->placeNewOrder($origin, $destination));
    }
}
