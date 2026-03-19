<?php

namespace Enadstack\CountryData\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Illuminate\Support\Collection;
use Enadstack\CountryData\Services\GeographyService;

/**
 * Cascading Country → City → Area Livewire dropdown.
 *
 * Basic usage (standalone form):
 *   <livewire:geography-select />
 *
 * With options:
 *   <livewire:geography-select
 *       :filter="'arab'"
 *       :locale="'ar'"
 *       :show-areas="true"
 *       country-field="address.country_code"
 *       city-field="address.city_id"
 *       area-field="address.area_id"
 *   />
 *
 * Pre-selected values:
 *   <livewire:geography-select
 *       selected-country="JO"
 *       :selected-city="$model->city_id"
 *       :selected-area="$model->area_id"
 *   />
 *
 * Listen for changes in a parent Livewire component:
 *   #[On('country-selected')]  public function onCountry(string $code) { ... }
 *   #[On('city-selected')]     public function onCity(int $cityId) { ... }
 *   #[On('area-selected')]     public function onArea(?int $areaId) { ... }
 */
class GeographySelect extends Component
{
    // ── Props ─────────────────────────────────────────────────────────────────

    /** Filter countries by tag: 'arab' | 'gulf' | null (all) */
    public ?string $filter = null;

    /** Label locale: 'en' | 'ar' */
    public string $locale = 'en';

    /** Show the areas dropdown */
    public bool $showAreas = true;

    /** Whether all three fields are required */
    public bool $required = false;

    /** HTML name attribute for the country field */
    public string $countryField = 'country_code';

    /** HTML name attribute for the city field */
    public string $cityField = 'city_id';

    /** HTML name attribute for the area field */
    public string $areaField = 'area_id';

    // ── State ─────────────────────────────────────────────────────────────────

    public string  $selectedCountry = '';
    public ?int    $selectedCity    = null;
    public ?int    $selectedArea    = null;

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    public function mount(
        ?string $filter         = null,
        string  $locale         = 'en',
        bool    $showAreas      = true,
        bool    $required       = false,
        string  $countryField   = 'country_code',
        string  $cityField      = 'city_id',
        string  $areaField      = 'area_id',
        string  $selectedCountry = '',
        ?int    $selectedCity   = null,
        ?int    $selectedArea   = null,
    ): void {
        $this->filter         = $filter         ?? config('country-data.livewire.filter');
        $this->locale         = $locale         ?? config('country-data.livewire.locale', 'en');
        $this->showAreas      = $showAreas      ?? config('country-data.livewire.show_areas', true);
        $this->required       = $required;
        $this->countryField   = $countryField;
        $this->cityField      = $cityField;
        $this->areaField      = $areaField;
        $this->selectedCountry = $selectedCountry;
        $this->selectedCity   = $selectedCity;
        $this->selectedArea   = $selectedArea;
    }

    // ── Watchers ──────────────────────────────────────────────────────────────

    public function updatedSelectedCountry(string $value): void
    {
        $this->selectedCity  = null;
        $this->selectedArea  = null;

        $this->dispatch('country-selected', code: $value);
    }

    public function updatedSelectedCity(?int $value): void
    {
        $this->selectedArea = null;

        $this->dispatch('city-selected', cityId: $value);
    }

    public function updatedSelectedArea(?int $value): void
    {
        $this->dispatch('area-selected', areaId: $value);
    }

    // ── Computed properties (cached per render cycle) ─────────────────────────

    #[Computed]
    public function countries(): Collection
    {
        return app(GeographyService::class)->countriesForSelect($this->locale, $this->filter);
    }

    #[Computed]
    public function cities(): Collection
    {
        if (! $this->selectedCountry) {
            return collect();
        }

        return app(GeographyService::class)->citiesForSelect($this->selectedCountry, $this->locale);
    }

    #[Computed]
    public function areas(): Collection
    {
        if (! $this->selectedCity || ! $this->showAreas) {
            return collect();
        }

        return app(GeographyService::class)->areasForSelect($this->selectedCity, $this->locale);
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('country-data::livewire.geography-select');
    }
}
