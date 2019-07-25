<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidateGeoCoordinatesRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     * only string type is accepted not flot or double
     *
     * @param  string  $attribute
     * @param  mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //check if valid lang long has been passed
        if (\count($value) !== 2
            || empty($value[0])
            || empty($value[1])
            || \gettype($value[0]) != "string"
            || \gettype($value[1]) != "string"
            || !\is_numeric($value[0])
            || !\is_numeric($value[1])
            || !self::_validateGeoCoordinates($value[0], $value[1])
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Validate the geo-coordinates
     *
     * @param float|int|string $lat Latitude
     * @param float|int|string $long Longitude
     *
     * @return boolean
     */
    protected function _validateGeoCoordinates($lat, $long)
    {
        return \preg_match('/^[-]?((([0-8]?[0-9])(\.(\d+))?)|(90(\.0+)?)),[-]?((((1[0-7][0-9])|([0-9]?[0-9]))(\.(\d+))?)|180(\.0+)?)$/', $lat . ',' . $long);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return \strtoupper(':attribute_INVALID_PARAMETERS');
    }
}
