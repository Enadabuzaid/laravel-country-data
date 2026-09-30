<?php

namespace Enadstack\CountryData\Models;

use Enadstack\CountryData\Enums\CountryCode;
use Enadstack\CountryData\Enums\Region;
use Enadstack\CountryData\Models\Concerns\HasRegionScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Region scopes (Country::europe(), ::gulf(), ::levant(), …) come from the
 * generated HasRegionScopes trait — one per Region case.
 */
class Country extends Model
{
    use HasRegionScopes;

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

    /**
     * Countries in a region: Country::inRegion(Region::Europe), ::inRegion('north-america').
     *
     * @throws \Enadstack\CountryData\Exceptions\RegionNotFoundException for an unknown name
     */
    public function scopeInRegion($query, Region|string $region)
    {
        $region = $region instanceof Region ? $region : Region::fromName($region);

        return $query->whereJsonContains('filters', $region->value);
    }

    /** One country by ISO-2 or ISO-3 code, case-insensitive: Country::code('jor')->first() */
    public function scopeCode($query, CountryCode|string $code)
    {
        if ($code instanceof CountryCode) {
            return $query->where('code', $code->value);
        }

        $code = strtoupper(trim($code));

        return match (strlen($code)) {
            2       => $query->where('code', $code),
            3       => $query->where('iso3', $code),
            default => $query->whereRaw('1 = 0'),
        };
    }

    /** Partial match on the English or Arabic, common or official name. */
    public function scopeSearch($query, string $term)
    {
        $like = '%' . trim($term) . '%';

        return $query->where(fn ($q) => $q
            ->where('name_en', 'like', $like)
            ->orWhere('name_ar', 'like', $like)
            ->orWhere('official_name_en', 'like', $like)
            ->orWhere('official_name_ar', 'like', $like)
        );
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
