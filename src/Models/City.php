<?php

namespace Enadstack\CountryData\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'country_id', 'country_code',
        'name_en', 'name_ar',
        'is_capital',
        'latitude', 'longitude',
        'population', 'timezone',
        'is_active',
    ];

    protected $casts = [
        'is_capital' => 'boolean',
        'is_active'  => 'boolean',
        'latitude'   => 'float',
        'longitude'  => 'float',
        'population' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCapitals($query)
    {
        return $query->where('is_capital', true);
    }

    public function scopeByCountry($query, string $countryCode)
    {
        return $query->where('country_code', strtoupper($countryCode));
    }

    // ── Accessors ─────────────────────────────────────────────────

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? ($this->name_ar ?? $this->name_en) : $this->name_en;
    }
}
