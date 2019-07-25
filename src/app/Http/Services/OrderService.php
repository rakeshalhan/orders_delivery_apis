<?php

namespace App\Http\Services;

use App\Helpers\DistanceHelper;
use App\Http\Models\Distance;
use App\Http\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

class OrderService
{
    /**
     * @var null|string
     */
    public $error = null;

    /**
     * @var int
     */
    public $errorCode;

    /**
     * @var DistanceHelper
     */
    protected $distanceHelper;

    /**
     * @param DistanceHelper $distanceHelper
     */
    public function __construct(DistanceHelper $distanceHelper)
    {
        $this->distanceHelper = $distanceHelper;
    }

    /**
     * Creating new order based on provided geo-coordinates
     *      save order for valid distance from origin to destination
     *
     * @param  array $origin
     * @param  array $destination
     *
     * @return \App\Http\Models\Order|boolean
     */
    public function placeNewOrder($origin, $destination)
    {
        $returnData = false;

        $this->distanceModel = $this->createDistanceModel();

        // check if origin and destination already exit in our system
        $this->distanceModel = $this->distanceModel->getFirstStoredDistanceObject($origin, $destination);
        $flagToSaveOrder = false;

        if (null === $this->distanceModel) {
            // if the distance doesn't existing in system, then evaluate the distance using map api and update the same in system
            $this->distanceModel = $this->distanceHelper->evaluateDistance($origin, $destination);

            // if any error in api then raise issue
            if (!($this->distanceModel instanceof \App\Http\Models\Distance)) {
                $this->error = $this->distanceModel;
                $this->errorCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
            } else {
                $flagToSaveOrder = true;
            }
        } else {
            $flagToSaveOrder = true;
        }

        if ($flagToSaveOrder) {
            // if the distance has been calculated and saved in system, then create a new order
            $orderModel = new \App\Http\Models\Order();
            $orderModel->saveOrder($this->distanceModel->id, $this->distanceModel->total_distance);

            $returnData = $orderModel;
        }

        return $returnData;
    }

    /**
     * Fetches list of order in system using given limit and page variable
     *
     * @param int $page
     * @param int $limit
     *
     * @return array
     */
    public function getList($page, $limit)
    {
        $page = (int) $page;
        $limit = (int) $limit;
        $orders = array();

        if ($page > 0 && $limit > 0) {
            $skip = ($page - 1) * $limit;
            $orders = (new \App\Http\Models\Order())->skip($skip)->take($limit)->orderBy('id', 'asc')->get();
        }

        return $orders;
    }

    /**
     * Fetches Order model based on primary key provided
     *
     * @param int $id
     *
     * @return \App\Http\Models\Order|null
     */
    public function getOrderById($id)
    {
        return \App\Http\Models\Order::findorfail($id);
    }

    /**
     * Mark an order as TAKEN, if not already
     *
     * @param int $orderId
     *
     * @return boolean
     */
    public function takeOrder($orderId)
    {
        $order = new \App\Http\Models\Order();

        return $order->takeOrder($orderId);
    }

    /**
     * Create a new distance model
     *
     * @return \App\Http\Models\Distance
     */
    public function createDistanceModel()
    {
        $distanceModel = new \App\Http\Models\Distance();

        return $distanceModel;
    }
}
