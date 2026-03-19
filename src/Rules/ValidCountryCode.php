<?php

namespace Enadstack\CountryData\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Enadstack\CountryData\Models\Country;

/**
 * Validates that a value is an active ISO-2 country code that exists in the DB.
 *
 * Usage:
 *   'country_code' => ['required', 'string', new ValidCountryCode],
 *
 * Optionally restrict to a filter group:
 *   'country_code' => ['required', new ValidCountryCode(filter: 'arab')],
 */
class ValidCountryCode implements ValidationRule
{
    public function __construct(
        private readonly ?string $filter = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = Country::active()
            ->where('code', strtoupper((string) $value))
            ->when($this->filter, fn ($q) => $q->byFilter($this->filter))
            ->exists();

        if (! $exists) {
            $fail($this->filter
                ? "The :attribute must be a valid {$this->filter} country code."
                : 'The :attribute must be a valid country code.'
            );
        }
    }
}
