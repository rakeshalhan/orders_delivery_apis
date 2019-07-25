<?php

use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

// to place new order using POST method
Route::post('orders', 'OrdersApiController@placeOrder');

// to take existing order using PATCH method
Route::patch('orders/{id}', 'OrdersApiController@takeOrder');

// to list all orders using GET method
Route::get('orders', 'OrdersApiController@listOrders');

Route::fallback(function () {
    $responseHelper = new ResponseHelper();
    return $responseHelper->sendResponse(array(
        'status' => 'error',
        'httpResponseCode' => JsonResponse::HTTP_NOT_FOUND,
        'message' => 'No route found',
    ));
});
