<?php

namespace Enadstack\CountryData\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Enadstack\CountryData\Models\Area;
use Enadstack\CountryData\Models\City;

/**
 * Validates that an area_id belongs to the specified city.
 *
 * Usage (in a FormRequest):
 *   'area_id' => ['nullable', 'integer', new ValidAreaForCity($this->input('city_id'))],
 *
 * Optionally restrict to a specific area type:
 *   'area_id' => ['nullable', new ValidAreaForCity($cityId, type: 'neighborhood')],
 */
class ValidAreaForCity implements ValidationRule
{
    public function __construct(
        private readonly int|City $city,
        private readonly ?string $type = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cityId = $this->city instanceof City ? $this->city->id : $this->city;

        $exists = Area::active()
            ->where('id', $value)
            ->where('city_id', $cityId)
            ->when($this->type, fn ($q) => $q->ofType($this->type))
            ->exists();

        if (! $exists) {
            $fail($this->type
                ? "The :attribute is not a valid {$this->type} for the selected city."
                : 'The :attribute is not a valid area for the selected city.'
            );
        }
    }
}
