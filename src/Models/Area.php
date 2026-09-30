<?php

namespace Enadstack\CountryData\Models;

use Enadstack\CountryData\Enums\AreaType;
use Enadstack\CountryData\Enums\CountryCode;
use Enadstack\CountryData\Exceptions\CountryNotFoundException;
use Enadstack\CountryData\Support\AreaCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $fillable = [
        'city_id', 'parent_id',
        'name_en', 'name_ar',
        'type',
        'latitude', 'longitude',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    // Available types (see also the AreaType enum)
    const TYPE_GOVERNORATE  = 'governorate';
    const TYPE_DISTRICT     = 'district';
    const TYPE_NEIGHBORHOOD = 'neighborhood';
    const TYPE_ZONE         = 'zone';
    const TYPE_STREET       = 'street';

    // ── Relationships ──────────────────────────────────────────────

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /** The district this area sits in (null for roots) */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'parent_id');
    }

    /** Neighborhoods / streets belonging to this district */
    public function children(): HasMany
    {
        return $this->hasMany(Area::class, 'parent_id');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, AreaType|string $type)
    {
        return $query->where('type', $type instanceof AreaType ? $type->value : $type);
    }

    /** Top-level areas only — districts, zones, anything with no parent */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /** Areas that hang off a district (named to avoid colliding with children()) */
    public function scopeNested($query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeDistricts($query)
    {
        return $query->where('type', self::TYPE_DISTRICT);
    }

    public function scopeNeighborhoods($query)
    {
        return $query->where('type', self::TYPE_NEIGHBORHOOD);
    }

    /**
     * Areas of one city: a City model, a city id, or a city's English name
     * (optionally narrowed to a country, since names repeat across countries).
     *
     *   Area::inCity($amman)   Area::inCity(12)   Area::inCity('Amman', 'JO')
     */
    public function scopeInCity($query, City|int|string $city, Country|CountryCode|string|null $country = null)
    {
        if ($city instanceof City || is_int($city)) {
            return $query->where('city_id', $city instanceof City ? $city->getKey() : $city);
        }

        return $query->whereIn('city_id', City::query()->select('id')
            ->where('name_en', $city)
            ->when($country !== null, fn (Builder $q) => $q->where('country_code', self::countryCode($country)))
        );
    }

    /** Areas of every city in a country (Country model, CountryCode, ISO-2 or ISO-3). */
    public function scopeInCountry($query, Country|CountryCode|string $country)
    {
        return $query->whereIn('city_id', City::query()->select('id')
            ->where('country_code', self::countryCode($country))
        );
    }

    /** @throws CountryNotFoundException for a string that is neither ISO-2 nor ISO-3 */
    private static function countryCode(Country|CountryCode|string $country): string
    {
        return match (true) {
            $country instanceof Country     => $country->code,
            $country instanceof CountryCode => $country->value,
            default                         => CountryCode::tryFromAny($country)?->value ?? strtoupper($country),
        };
    }

    public function newCollection(array $models = []): AreaCollection
    {
        return new AreaCollection($models);
    }

    // ── Accessors ─────────────────────────────────────────────────

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? ($this->name_ar ?? $this->name_en) : $this->name_en;
    }
}
