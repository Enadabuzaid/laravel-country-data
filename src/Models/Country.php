<?php

namespace Enadstack\CountryData\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Country extends Model
{
    protected $fillable = [
        'code', 'iso2', 'iso3', 'numeric_code', 'cioc',
        'name_en', 'name_ar', 'official_name_en', 'official_name_ar',
        'flag', 'emoji', 'flag_png', 'flag_svg',
        'coat_of_arms_png', 'coat_of_arms_svg',
        'google_maps_url', 'openstreet_maps_url',
        'currency_code', 'currency_name_en', 'currency_name_ar',
        'currency_symbol_en', 'currency_symbol_ar',
        'dial', 'capital', 'region', 'continent', 'subregion',
        'languages', 'timezones', 'tld', 'borders', 'filters',
        'latitude', 'longitude', 'population', 'area',
        'is_active',
    ];

    protected $casts = [
        'languages' => 'array',
        'timezones' => 'array',
        'tld'       => 'array',
        'borders'   => 'array',
        'filters'   => 'array',
        'latitude'  => 'float',
        'longitude' => 'float',
        'population'=> 'integer',
        'area'      => 'float',
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    /**
     * The capital city. Eager-load it to avoid one query per country:
     *   Country::with('capitalCity')->get()
     */
    public function capitalCity(): HasOne
    {
        return $this->hasOne(City::class)->where('is_capital', true);
    }

    /**
     * All areas for this country through its cities.
     *
     * Enables:
     *   $jordan->areas                                   // all areas
     *   $jordan->areas()->ofType('neighborhood')->get()  // filtered by type
     *   $jordan->areas()->where('name_en', 'Abdoun')->first()
     */
    public function areas(): HasManyThrough
    {
        return $this->hasManyThrough(Area::class, City::class);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByFilter($query, string $filter)
    {
        return $query->whereJsonContains('filters', $filter);
    }

    // ── Accessors ─────────────────────────────────────────────────

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? ($this->name_ar ?? $this->name_en) : $this->name_en;
    }

    /**
     * Backward-compatible `$country->capital_city`. Reads through the
     * capitalCity relation, so it queries at most once and uses eager loads.
     */
    public function getCapitalCityAttribute(): ?City
    {
        return $this->getRelationValue('capitalCity');
    }
}
