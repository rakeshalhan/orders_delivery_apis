<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Helpers\ResponseHelper;

class CommonFormRequest extends FormRequest
{
    const ERROR = 'error';

    /** @var Response */
    protected $responseHelper;

    /**
     * @param Response $responseHelper
     */
    public function __construct(ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // make it true to mark user as authorized by default
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        //Currectly considering only first error
        $firstError = array_values($errors)[0][0];

        throw new HttpResponseException(
            $this->responseHelper->sendResponse(array(
                'status' => self::ERROR,
                'httpResponseCode' => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
                'message' => $firstError,
            ))
        );
    }
}
