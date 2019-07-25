<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Models\Order;
use App\Http\Requests\OrdersListRequest;
use App\Http\Requests\OrdersPlaceRequest;
use App\Http\Requests\OrdersTakeRequest;
use App\Http\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class to manage RESTful API collection for orders
 *
 * @author Rakesh Alhan <rakesh.alhan@nagarro.com>
 */
class OrdersApiController extends Controller
{
    const SUCCESS = 'Success';
    const ERROR = 'error';

    /**
     * Response helper
     *
     * @var ResponseHelper
     */
    protected $responseHelper;

    /**
     * Order service
     *
     * @var OrderService
     */
    protected $orderService;

    public function __construct(OrderService $orderService, ResponseHelper $responseHelper)
    {
        $this->orderService = $orderService;
        $this->responseHelper = $responseHelper;
    }

    /**
     * Creating new order for provided origin and destination
     *
     * @param OrdersPlaceRequest $request
     *
     * @return json
     */
    public function placeOrder(OrdersPlaceRequest $request)
    {
        $returnData = array(
            'status' => self::ERROR,
            'response' => array(
                'code' => null,
                'data' => null,
            ),
        );

        $origin = $request->input('origin');
        $destination = $request->input('destination');

        try {
            $orderModel = $this->orderService->placeNewOrder($origin, $destination);

            if (false != $orderModel) {
                $returnData['response'] = array(
                    'code' => JsonResponse::HTTP_OK,
                    'data' => array(
                        'id' => $orderModel->id,
                        'distance' => $orderModel->getDistance(),
                        'status' => $orderModel->status,
                    ),
                );
                $returnData['status'] = self::SUCCESS;
            } else {
                $returnData['response'] = array(
                    'code' => $this->orderService->errorCode,
                    'data' => $this->orderService->error,
                );
            }
        } catch (\Exception $ex) {
            $returnData['response'] = array(
                'code' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                'data' => $ex->getMessage(),
            );
        }

        return $this->responseHelper->sendResponse(array(
            'status' => $returnData['status'],
            'httpResponseCode' => $returnData['response']['code'],
            'message' => $returnData['response']['data'],
            'send_message_as_core' => self::SUCCESS == $returnData['status'],
        ));
    }

    /**
     * Taking the order
     *  - it will change the status of valid order to TAKEN if successfully ASSIGNED and NOT ALREADY TAKEN
     *
     * @param  OrdersTakeRequest $request
     * @param  int $id
     *
     * @return json
     */
    public function takeOrder(OrdersTakeRequest $request, $id)
    {
        $returnData = array(
            'status' => self::ERROR,
            'response' => array(
                'code' => null,
                'data' => null,
            ),
        );

        if ((int) $id > 0) {
            try {
                $order = $this->orderService->getOrderById((int) $id);

                if ($order->status == Order::ASSIGNED_ORDER_STATUS) {
                    // if order has been already taken
                    $returnData['response'] = array(
                        'code' => JsonResponse::HTTP_CONFLICT,
                        'data' => 'ALREADY_TAKEN',
                    );
                } elseif (false === $this->orderService->takeOrder((int) $id)) {
                    // if taking the order is facing some issue, against race condition
                    $returnData['response'] = array(
                        'code' => JsonResponse::HTTP_CONFLICT,
                        'data' => 'ALREADY_TAKEN',
                    );
                } else {
                    $returnData['response'] = array(
                        'code' => JsonResponse::HTTP_OK,
                        'data' => array('status' => 'SUCCESS'),
                    );
                    $returnData['status'] = self::SUCCESS;
                }
            } catch (\Exception $ex) {
                $returnData['response'] = array(
                    'code' => JsonResponse::HTTP_EXPECTATION_FAILED,
                    'data' => 'INVALID_ORDER',
                );
            }
        } else {
            $returnData['response'] = array(
                'code' => JsonResponse::HTTP_NOT_FOUND,
                'data' => 'INVALID_ORDER',
            );
        }

        return $this->responseHelper->sendResponse(array(
            'status' => $returnData['status'],
            'httpResponseCode' => $returnData['response']['code'],
            'message' => $returnData['response']['data'],
            'send_message_as_core' => self::SUCCESS == $returnData['status'],
        ));
    }

    /**
     * Listing the orders as per pagination
     *
     * @param  OrdersListRequest $request
     *
     * @return json
     */
    public function listOrders(OrdersListRequest $request)
    {
        $returnData = array(
            'status' => self::ERROR,
            'response' => array(
                'code' => null,
                'data' => null,
            ),
        );

        try {
            $page = (int) $request->get('page', 1);
            $limit = (int) $request->get('limit', 1);

            $records = $this->orderService->getList($page, $limit);

            $orders = array();
            if ($records && ($records->count() > 0)) {
                foreach ($records as $order) {
                    $orders[] = array('id' => $order->id, 'distance' => $order->getDistance(), 'status' => $order->status);
                }
            }

            //send orders
            $returnData['response'] = array(
                'code' => JsonResponse::HTTP_OK,
                'data' => $orders,
            );
            $returnData['status'] = self::SUCCESS;
        } catch (\Exception $ex) {
            $returnData['response'] = array(
                'code' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                'data' => $ex->getMessage(),
            );
        }

        return $this->responseHelper->sendResponse(array(
            'status' => $returnData['status'],
            'httpResponseCode' => $returnData['response']['code'],
            'message' => $returnData['response']['data'],
            'send_message_as_core' => self::SUCCESS == $returnData['status'],
        ));
    }
}
