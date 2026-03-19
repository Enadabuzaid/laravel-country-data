<?php

namespace Enadstack\CountryData\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Area extends Model
{
    protected $fillable = [
        'city_id',
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

    // Available types
    const TYPE_GOVERNORATE  = 'governorate';
    const TYPE_DISTRICT     = 'district';
    const TYPE_NEIGHBORHOOD = 'neighborhood';
    const TYPE_ZONE         = 'zone';

    // ── Relationships ──────────────────────────────────────────────

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ── Accessors ─────────────────────────────────────────────────

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? ($this->name_ar ?? $this->name_en) : $this->name_en;
    }
}
