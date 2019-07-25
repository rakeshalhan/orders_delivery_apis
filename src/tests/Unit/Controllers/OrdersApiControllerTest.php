<?php

use App\Http\Controllers\OrdersApiController;
use App\Http\Models\Order;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\JsonResponse;

class OrdersApiControllerTest extends Tests\TestCase
{
    use WithoutMiddleware;

    protected static $allowedOrderStatus = array(
        Order::UNASSIGNED_ORDER_STATUS,
        Order::ASSIGNED_ORDER_STATUS
    );

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();
        $this->orderServiceMock = \Mockery::mock(\App\Http\Services\OrderService::class);

        $this->responseHelper = \App::make(\App\Helpers\ResponseHelper::class);

        $this->app->instance(OrdersApiController::class,
            new OrdersApiController(
                $this->orderServiceMock,
                $this->responseHelper
            )
        );
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    public function testOrderPlace_PositiveTest()
    {
        echo "\n >>>>> Unit test:: Controller: OrdersApiController - Method: placeOrder - Positive test - Expected response code: 200 - Expected keys in order details: id, distance";

        $order = $this->generateFakeOrder();

        $params = [
            'origin' => array(\strval($this->faker->latitude()), \strval($this->faker->longitude())),
            'destination' => array(\strval($this->faker->latitude()), \strval($this->faker->longitude())),
        ];

        //Order Service will return success
        $this->orderServiceMock
            ->shouldReceive('placeNewOrder')
            ->with($params['origin'], $params['destination'])
            ->once()
            ->andReturn($order);

        $response = $this->call('POST', '/orders', $params);
        $data = $response->decodeResponseJson();

        $response->assertStatus(200);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('distance', $data);
    }

    public function testOrderPlace_NegativeTest_NoOrigin()
    {
        echo "\n >>>>> Unit test:: Controller: OrdersApiController - Method: placeOrder - Negative test - Missing param: origin - Expected response code: 422 - Expected keys: error";

        $order = $this->generateFakeOrder();

        $params = [
            //'origin' => array(\strval($this->faker->latitude()), \strval($this->faker->longitude())),
            'destination' => array(\strval($this->faker->latitude()), \strval($this->faker->longitude())),
        ];

        //Order Service will return failure
        $this->orderServiceMock
            ->shouldReceive('placeNewOrder')
            ->andReturn(false);

        $this->orderServiceMock->error = 'INVALID_PARAMETERS';
        $this->orderServiceMock->errorCode = JsonResponse::HTTP_UNPROCESSABLE_ENTITY;

        $response = $this->call('POST', '/orders', $params);
        $data = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
    }

    public function testOrderPlace_NegativeTest_InvalidLatLong()
    {
        echo "\n >>>>> Unit test:: Controller: OrdersApiController - Method: placeOrder - Negative test - Invalid param values - Expected response code: 422 - Expected keys: error";

        $order = $this->generateFakeOrder();

        $params = [
            'origin' => array(\strval($this->faker->latitude(100)), \strval($this->faker->longitude())),
            'destination' => array(\strval($this->faker->latitude()), \strval($this->faker->longitude())),
        ];

        //Order Service will return failure
        $this->orderServiceMock
            ->shouldReceive('placeNewOrder')
            ->andReturn(false);

        $this->orderServiceMock->error = 'INVALID_PARAMETERS';
        $this->orderServiceMock->errorCode = JsonResponse::HTTP_UNPROCESSABLE_ENTITY;

        $response = $this->call('POST', '/orders', $params);
        $data = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
    }

    public function testOrderPlace_NegativeTest_ExceptionInternalServerError()
    {
        echo "\n >>>>> Unit test:: Controller: OrdersApiController - Method: placeOrder - Negative test - Exception: InternalServerError - Expected response code: 500 - Expected keys: error";

        $order = $this->generateFakeOrder();

        $params = [
            'origin' => array(\strval($this->faker->latitude()), \strval($this->faker->longitude())),
            'destination' => array(\strval($this->faker->latitude()), \strval($this->faker->longitude())),
        ];

        //Order Service will return failure
        $this->orderServiceMock
            ->shouldReceive('placeNewOrder')
            ->andThrow(new \InvalidArgumentException());

        $this->orderServiceMock->error = 'Invalid_Argument_Exception';
        $this->orderServiceMock->errorCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;

        $response = $this->call('POST', '/orders', $params);
        $data = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
    }

    public function testOrderTake_PositiveTest()
    {
        echo "\n >>>>> Unit test:: Controller: OrdersApiController - Method: takeOrder - Positive test - Expected response code: 200 - Expected keys value: success";

        $id = $this->faker->numberBetween(1, 99999);

        $order = $this->generateFakeOrder($id);

        //update order status as "UNASSIGNED"
        $order->status = Order::UNASSIGNED_ORDER_STATUS;

        $this->orderServiceMock
            ->shouldReceive('getOrderById')
            ->once()
            ->with($id)
            ->andReturn($order);

        $this->orderServiceMock
            ->shouldReceive('takeOrder')
            ->once()
            ->with($id)
            ->andReturn(true);

        $params = array('status' => 'TAKEN');

        $response = $this->call('PATCH', "/orders/{$id}", $params);
        $data = (array) $response->decodeResponseJson();

        $response->assertStatus(JsonResponse::HTTP_OK);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertEquals('SUCCESS', $data['status']);
    }

    public function testOrderTakeInValid_NegativeCase_InvalidParamater()
    {
        echo "\n >>>>> Unit test:: Controller: OrdersApiController - Method: takeOrder - Negative test - Invalid params - Expected response code: 422 - Expected keys: error";

        $id = $this->faker->numberBetween(1, 99999);

        $order = $this->generateFakeOrder($id);

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getOrderById')
            ->with($id)
            ->andReturn(true);

        $params = array('status' => 'ASSIGNED');

        $response = $this->call('PATCH', "/orders/{$id}", $params);
        $data = (array) $response->decodeResponseJson();

        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('STATUS_IS_INVALID', $data['error']);
    }

    public function testOrderTake_NegativeCase_InvalidId()
    {
        echo "\n >>>>> Unit test:: Controller: OrdersApiController - Method: takeOrder - Negative test - Invalid order-id - Expected response code: 417 - Expected keys: error";

        $id = $this->faker->numberBetween(499999, 999999);

        $order = $this->generateFakeOrder($id);

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getOrderById')
            ->once()
            ->with($id)
            ->andThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $params = array('status' => 'TAKEN');

        $response = $this->call('PATCH', "/orders/{$id}", $params);
        $data = (array) $response->getData();
        $response->assertStatus(JsonResponse::HTTP_EXPECTATION_FAILED);

        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('INVALID_ID', $data['error']);
    }

    public function testOrderTake_NegativeTest_AlreadyTaken()
    {
        echo "\n >>>>> Unit test:: Controller: OrdersApiController - Method: takeOrder - Negative test - Order-id already taken - Expected response code: 409 - Expected keys: error";

        $id = $this->faker->numberBetween(1, 99999);

        $order = $this->generateFakeOrder($id);

        //status should already taken
        $order->status = Order::ASSIGNED_ORDER_STATUS;

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getOrderById')
            ->once()
            ->with($id)
            ->andReturn($order);

        $params = array('status' => 'TAKEN');

        $response = $this->call('PATCH', "/orders/{$id}", $params);
        $data = (array) $response->decodeResponseJson();

        $response->assertStatus(JsonResponse::HTTP_CONFLICT);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('ORDER_ALREADY_BEEN_TAKEN', $data['error']);
    }

    public function testOrderTake_NegativeTest_RaceCondition()
    {
        echo "\n >>>>> Unit test:: Controller: OrdersApiController - Method: takeOrder - Negative test - Race condition - Expected response code: 409 - Expected keys: error";

        $id = $this->faker->numberBetween(1, 99999);

        $order = $this->generateFakeOrder($id);

        $order->status = Order::UNASSIGNED_ORDER_STATUS;

        // valid order id with status UNASSIGNED
        $this->orderServiceMock
            ->shouldReceive('getOrderById')
            ->once()
            ->with($id)
            ->andReturn($order);

        // valid order id, but taken by another request in parallel
        $this->orderServiceMock
            ->shouldReceive('takeOrder')
            ->once()
            ->with($id)
            ->andReturn(false);

        $params = array('status' => 'TAKEN');

        $response = $this->call('PATCH', "/orders/{$id}", $params);
        $data = (array) $response->decodeResponseJson();

        $response->assertStatus(JsonResponse::HTTP_CONFLICT);
        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('ORDER_ALREADY_BEEN_TAKEN', $data['error']);
    }

    public function testOrderList_PositiveTest()
    {
        echo "\n >>>>> Unit test:: Controller: OrdersApiController - Method: listOrder - Positive test - Expected response code: 200 - Expected keys in record details: id, distance, status";

        $page = 1;
        $limit = 5;

        $orderList = array();

        for ($i = 0; $i < 5; $i++) {
            $orderList[] = $this->generateFakeOrder();
        }

        $orderRecordCollection = new \Illuminate\Database\Eloquent\Collection($orderList);

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getList')
            ->once()
            ->with($page, $limit)
            ->andReturn($orderRecordCollection);

        $params = array('page' => $page, 'limit' => $limit);

        $response = $this->call('GET', "/orders", $params);
        $data = $response->getData();

        $response->assertStatus(JsonResponse::HTTP_OK);

        $this->assertInternalType('array', $data);
        $this->assertArrayHasKey('id', (array) $data[0]);
        $this->assertArrayHasKey('distance', (array) $data[0]);
        $this->assertArrayHasKey('status', (array) $data[0]);
    }

    public function testOrderList_PositiveTest_BeyondTheRecordCount()
    {
        echo "\n >>>>> Unit test:: Controller: OrdersApiController - Method: listOrder - Positive test - Beyond the record count - Expected response code: 200 - Expected record count: 0";

        $page = 999999;
        $limit = 5;

        $orderRecordCollection = new \Illuminate\Database\Eloquent\Collection([]);

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getList')
            ->once()
            ->andReturn($orderRecordCollection);

        $params = array('page' => $page, 'limit' => $limit);

        $response = $this->call('GET', "/orders", $params);
        $data = $response->getData();

        $response->assertStatus(JsonResponse::HTTP_OK);
        $this->assertEquals(0, \count($data));
    }

    public function testOrderList_NegativeTest_InvalidParamValue()
    {
        echo "\n >>>>> Unit test:: Controller: OrdersApiController - Method: listOrder - Negative test - Invalid param value: A - Expected response code: 422 - Expected keys: error";

        $page = 'A';
        $limit = 5;

        $orderRecordCollection = new \Illuminate\Database\Eloquent\Collection([]);

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getList')
            ->andReturn($orderRecordCollection);

        $params = array('page' => $page, 'limit' => $limit);

        $response = $this->call('GET', "/orders", $params);
        $data = $response->getData();

        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testOrderList_NegativeTest_ExceptionInternalServerError()
    {
        echo "\n >>>>> Unit test:: Controller: OrdersApiController - Method: listOrder - Negative test - Negative test - Exception: InternalServerError - Expected response code: 500 - Expected keys: error";

        $page = 1;
        $limit = 5;

        $orderRecordCollection = new \Illuminate\Database\Eloquent\Collection([]);

        //In Valid order id provided
        $this->orderServiceMock
            ->shouldReceive('getList')
            ->andThrow(new \InvalidArgumentException());

        $params = array('page' => $page, 'limit' => $limit);

        $response = $this->call('GET', "/orders", $params);
        $data = $response->getData();

        $response->assertStatus(JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param int|null $id
     *
     * @return Order
     */
    private function generateFakeOrder($id = null)
    {
        $id = $id ? $id : $this->faker->numberBetween(1, 9999);

        $order = new Order();
        $order->id = $id;
        $order->status = $this->faker->randomElement(self::$allowedOrderStatus);
        $order->distance_id = $this->faker->numberBetween(1, 9999);
        $order->total_distance = $this->faker->numberBetween(10, 9999);
        $order->created_at = $this->faker->dateTimeBetween();
        $order->updated_at = $this->faker->dateTimeBetween();

        return $order;
    }

}
