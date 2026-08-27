<?php

namespace Enadstack\CountryData\Models;

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

    // Available types
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

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
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

    // ── Accessors ─────────────────────────────────────────────────

    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' ? ($this->name_ar ?? $this->name_en) : $this->name_en;
    }
}
