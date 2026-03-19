<?php

namespace Enadstack\CountryData\Data;

/**
 * Immutable value object representing a currency.
 *
 * Usage:
 *   $currency = Geography::currencyOf('JO');
 *   $currency->code;          // 'JOD'
 *   $currency->name();        // locale-aware name
 *   $currency->symbol();      // locale-aware symbol
 *   $currency->in('ar')->name(); // Arabic name
 *   $currency->toArray();
 */
final class CurrencyData
{
    public function __construct(
        public readonly string $code,
        public readonly string $nameEn,
        public readonly string $nameAr,
        public readonly string $symbolEn,
        public readonly string $symbolAr,
        private readonly string $locale = 'en',
    ) {}

    /** Locale-aware display name */
    public function name(): string
    {
        return $this->locale === 'ar' ? ($this->nameAr ?: $this->nameEn) : $this->nameEn;
    }

    /** Locale-aware display symbol */
    public function symbol(): string
    {
        return $this->locale === 'ar' ? ($this->symbolAr ?: $this->symbolEn) : $this->symbolEn;
    }

    /** Return a copy of this object in a different locale */
    public function in(string $locale): self
    {
        return new self(
            $this->code,
            $this->nameEn,
            $this->nameAr,
            $this->symbolEn,
            $this->symbolAr,
            $locale,
        );
    }

    public function toArray(): array
    {
        return [
            'code'      => $this->code,
            'name'      => $this->name(),
            'name_en'   => $this->nameEn,
            'name_ar'   => $this->nameAr,
            'symbol'    => $this->symbol(),
            'symbol_en' => $this->symbolEn,
            'symbol_ar' => $this->symbolAr,
        ];
    }
}
