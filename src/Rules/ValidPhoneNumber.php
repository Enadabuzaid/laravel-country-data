<?php

namespace Enadstack\CountryData\Rules;

use Closure;
use Enadstack\CountryData\Models\Country;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;

/**
 * Validates a national phone number against the dial code of the country
 * selected in a sibling field (ISO-2), keeping the full number E.164-sized.
 *
 * Usage:
 *   'phone_country_code' => ['required_with:phone', new ValidCountryCode],
 *   'phone'              => ['nullable', new ValidPhoneNumber('phone_country_code')],
 *
 * Normalise input first with ValidPhoneNumber::normalize() so "0772 432 330"
 * is stored as "772432330".
 */
class ValidPhoneNumber implements DataAwareRule, ValidationRule
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function __construct(
        private readonly string $countryCodeField = 'phone_country_code',
        private readonly int $minDigits = 6,
    ) {}

    /**
     * Strip formatting characters and the national trunk prefix (leading zeros).
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = ltrim(preg_replace('/\D+/', '', $phone) ?? '', '0');

        return $digits === '' ? null : $digits;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! is_int($value)) {
            $fail('The :attribute must be a valid phone number.');

            return;
        }

        $digits = (string) $value;

        if (! ctype_digit($digits)) {
            $fail('The :attribute may only contain digits.');

            return;
        }

        $countryCode = Arr::get($this->data, $this->countryCodeField);

        if (! is_string($countryCode) || $countryCode === '') {
            $fail('Select a country code for the :attribute.');

            return;
        }

        $dial = Country::active()->where('code', strtoupper($countryCode))->value('dial');

        if ($dial === null) {
            // The country field reports its own error via ValidCountryCode.
            return;
        }

        $dialDigits = preg_replace('/\D+/', '', (string) $dial) ?? '';

        if (strlen($digits) < $this->minDigits || strlen($dialDigits.$digits) > 15) {
            $fail("The :attribute is not a valid number for {$dial}.");
        }
    }
}
