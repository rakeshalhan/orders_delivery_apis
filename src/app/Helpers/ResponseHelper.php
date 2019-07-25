<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Lang;

/**
 * To transform and send the response code/message
 */
class ResponseHelper
{
    const SUCCESS = 'success';
    const ERROR = 'error';

    /**
     * To transform and send response in JSON format, along with status
     *
     * @param array $responseDataArray this param array can have 3 values: status, httpResponseCode, message, send_message_as_core
     *
     * @return json
     */
    public function sendResponse($responseDataArray)
    {
        $responseDataArray['status'] = (isset($responseDataArray['status']) && \in_array(\strtolower($responseDataArray['status']), array(self::SUCCESS, self::ERROR))) ? \strtolower($responseDataArray['status']) : null;
        $responseDataArray['httpResponseCode'] = isset($responseDataArray['httpResponseCode']) ? $responseDataArray['httpResponseCode'] : JsonResponse::HTTP_BAD_REQUEST;
        $responseDataArray['message'] = isset($responseDataArray['message']) ? $responseDataArray['message'] : '';
        $responseDataArray['send_message_as_core'] = isset($responseDataArray['send_message_as_core']) ? !!$responseDataArray['send_message_as_core'] : false;
        if (\is_string($responseDataArray['message']) && '' !== $responseDataArray['message']) {
            $responseDataArray['message'] = $this->_getLocaleMessage($responseDataArray['message']);
        }

        $responseDataMessage = ($responseDataArray['send_message_as_core'] || !$responseDataArray['status']) ? $responseDataArray['message'] : array($responseDataArray['status'] => $responseDataArray['message']);
        return response()->json($responseDataMessage, $responseDataArray['httpResponseCode']);
    }

    /**
     * To transform the response code to message, based on locale
     *
     * @param  string $key
     *
     * @return string
     */
    protected function _getLocaleMessage($key)
    {
        return Lang::has('message.' . $key) ? __('message.' . $key) : $key;
    }
}
