<?php

namespace App\Test\Feature\ApiController;

use Tests\TestCase;

class OrdersApiIntegrationTest extends TestCase
{
    public function testOrderPlace_IncorrectParameters()
    {
        echo "\n >>>>> Integration test:: Place order - Negative test - Invalid parameter keys - Expected response code: 422 - Un-processable entity";

        $inputData = array(
            'origin1' => array(
                '29.15394',
                '75.72294',
            ),
            'destination' => array(
                '28.4601',
                '77.02635',
            ),
        );
        $response = $this->json('POST', '/orders', $inputData);

        $response->assertStatus(422);
    }

    public function testOrderPlace_EmptyParameters()
    {
        echo "\n >>>>> Integration test:: Place order - Negative test - Invalid parameter values - Expected response code: 422 - Un-processable entity";

        $inputData = array(
            'origin' => array(
                '29.15394',
                '',
            ),
            'destination' => array(
                '28.4601',
                '77.02635',
            ),
        );
        $response = $this->json('POST', '/orders', $inputData);

        $response->assertStatus(422);
    }

    public function testOrderPlace_AdditionalParameters()
    {
        echo "\n >>>>> Integration test:: Place order - Negative test - Additional parameter values - Expected response code: 422 - Un-processable entity";

        $inputData = array(
            'origin' => array(
                '29.15394',
                '75.72294',
                '75.12345',
            ),
            'destination' => array(
                '28.4601',
                '77.02635',
            ),
        );
        $response = $this->json('POST', '/orders', $inputData);

        $response->assertStatus(422);
    }

    public function testOrderPlace_InvalidData_OutOfRangeData()
    {
        echo "\n >>>>> Integration test:: Place order - Negative test - Out of range parameter values - Expected response code: 422 - Un-processable entity";

        $inputData = array(
            'origin' => array(
                '100.111111',
                '44.222222',
            ),
            'destination' => array(
                '28.4601',
                '77.02635',
            ),
        );
        $response = $this->json('POST', '/orders', $inputData);

        $response->assertStatus(422);
    }

    public function testOrderPlace_PositiveScenario()
    {
        echo "\n >>>>> Integration test:: Place order - Positive test - Valid parameter values - Expected response code: 200 - Expected keys in order details: id, status and distance";

        $validData = array(
            'origin' => array(
                '29.15394',
                '75.72294',
            ),
            'destination' => array(
                '28.486010',
                '77.074096',
            ),
        );
        $response = $this->json('POST', '/orders', $validData);
        $data = (array) $response->getData();

        $response->assertStatus(200);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('distance', $data);
    }

    /**
     * Performing all tests in single funcation, because we're making single database entry for all such tests
     */
    public function testOrderTake_AllScenarios()
    {
        echo "\n >>>>> Integration test:: Take order - All tests: positive and negative";

        echo "\n >>>>> Initialization:: Place order to test update processes";

        $validData = array(
            'origin' => array(
                '28.704060',
                '77.102493',
            ),
            'destination' => array(
                '28.535517',
                '77.391029',
            ),
        );

        $updateData = array('status' => 'TAKEN');
        $response = $this->json('POST', '/orders', $validData);
        $data = (array) $response->getData();
        $orderId = $data['id'];
        echo "\n >>>>> Initialization:: Order (ID: " . $orderId . ") has been placed successfully";

        echo "\n >>>>> Integration test:: Take order - Positive test - Expected response code: 200 - Expected keys: status";
        $response = $this->json('PATCH', '/orders/' . $orderId, $updateData);
        $data = (array) $response->getData();

        $response->assertStatus(200);
        $this->assertArrayHasKey('status', $data);

        echo "\n >>>>> Integration test:: Take order - Negative test - Already taken - Expected response code: 409 - Expected keys: error";
        $this->_performOrderTake_NegativeTest($orderId, array('status' => 'TAKEN'), 409);

        echo "\n >>>>> Integration test:: Take order - Negative test - Invalid param key: status1 - Expected response code: 422 - Expected keys: error";
        $this->_performOrderTake_NegativeTest($orderId, array('status1' => 'TAKEN'), 422);

        echo "\n >>>>> Integration test:: Take order - Negative test - Invalid param value: TAKEN1 - Expected response code: 422 - Expected keys: error";
        $this->_performOrderTake_NegativeTest($orderId, array('status' => 'TAKEN1'), 422);

        echo "\n >>>>> Integration test:: Take order - Negative test - Empty param value: TAKEN1 - Expected response code: 422 - Expected keys: error";
        $this->_performOrderTake_NegativeTest($orderId, array('status' => ''), 422);

        echo "\n >>>>> Integration test:: Take order - Negative test - Invalid order-id - Expected response code: 417 - Expected keys: error";
        $this->_performOrderTake_NegativeTest(9999999, array('status' => 'TAKEN'), 417);
    }

    protected function _performOrderTake_NegativeTest($orderId, $params, $expectedResponseHttpCode)
    {
        $response = $this->json('PATCH', '/orders/' . $orderId, $params);
        $data = (array) $response->getData();

        $response->assertStatus($expectedResponseHttpCode);
        $this->assertArrayHasKey('error', $data);
    }

    /**
     * Integration test:: List orders
     */
    public function testOrderList_SuccessWithData()
    {
        echo "\n >>>>> Integration test:: List orders - Positive test - Correct result count: page=1&limit=5 - Expected response code: 200 - Expected keys in order details: id, distance, status";

        $query = 'page=1&limit=5';
        $response = $this->json('GET', "/orders?$query", array());
        $data = (array) $response->getData();

        $response->assertStatus(200);
        $this->assertLessThan(6, \count($data));

        foreach ($data as $order) {
            $order = (array) $order;
            $this->assertArrayHasKey('id', $order);
            $this->assertArrayHasKey('distance', $order);
            $this->assertArrayHasKey('status', $order);
        }
    }

    public function testOrderList_SuccessNoData()
    {
        echo "\n >>>>> Integration test:: List orders - Positive test - Correct result count: page=99999&limit=5 - Expected response code: 200 - Expected record count would be zero";

        $query = 'page=99999&limit=5';
        $response = $this->json('GET', "/orders?$query", array());
        $data = (array) $response->getData();

        $response->assertStatus(200);
        $this->assertEquals(0, \count($data));
    }

    public function testOrderList_Failure()
    {
        echo "\n >>>>> Integration test:: List orders - Negative test - Invalid param: page1 - Expected response code: 422 - Expected keys: error";
        $queryString = 'page1=1&limit=4';
        $this->_performOrderList_NegativeTest($queryString, 422);

        echo "\n >>>>> Integration test:: List orders - Negative test - Invalid param: limit1 - Expected response code: 422 - Expected keys: error";
        $queryString = 'page=1&limit1=4';
        $this->_performOrderList_NegativeTest($queryString, 422);

        echo "\n >>>>> Integration test:: List orders - Negative test - Invalid param value: page=0 - Expected response code: 422 - Expected keys: error";
        $queryString = 'page=0&limit=4';
        $this->_performOrderList_NegativeTest($queryString, 422);

        echo "\n >>>>> Integration test:: List orders - Negative test - Invalid param value: limit=0 - Expected response code: 422 - Expected keys: error";
        $queryString = 'page=1&limit=0';
        $this->_performOrderList_NegativeTest($queryString, 422);

        echo "\n >>>>> Integration test:: List orders - Negative test - Invalid param value: page=-1 - Expected response code: 422 - Expected keys: error";
        $queryString = 'page=-1&limit=0';
        $this->_performOrderList_NegativeTest($queryString, 422);

        echo "\n >>>>> Integration test:: List orders - Negative test - Invalid param value: limit=-1 - Expected response code: 422 - Expected keys: error";
        $queryString = 'page=1&limit=-1';
        $this->_performOrderList_NegativeTest($queryString, 422);
    }

    protected function _performOrderList_NegativeTest($queryString, $expectedResponseHttpCode)
    {
        $response = $this->json('GET', "/orders?$queryString", array());
        $data = (array) $response->getData();

        $response->assertStatus($expectedResponseHttpCode);
        $this->assertArrayHasKey('error', $data);
    }
}
