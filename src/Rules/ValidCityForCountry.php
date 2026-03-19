<?php

namespace Enadstack\CountryData\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Enadstack\CountryData\Models\City;

/**
 * Validates that a city_id belongs to the specified country.
 *
 * Usage (in a FormRequest):
 *   'city_id' => ['required', 'integer', new ValidCityForCountry($this->input('country_code'))],
 *
 * Usage (in a controller):
 *   $request->validate([
 *       'city_id' => ['required', new ValidCityForCountry($request->country_code)],
 *   ]);
 */
class ValidCityForCountry implements ValidationRule
{
    public function __construct(
        private readonly string $countryCode
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = City::active()
            ->where('id', $value)
            ->where('country_code', strtoupper($this->countryCode))
            ->exists();

        if (! $exists) {
            $fail('The :attribute is not a valid city for the selected country.');
        }
    }
}
