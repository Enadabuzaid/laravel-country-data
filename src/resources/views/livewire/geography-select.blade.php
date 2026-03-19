{{--
    Geography Select — Livewire cascading dropdown (Country → City → Area)

    Publish to customise:
        php artisan vendor:publish --tag=country-data-livewire

    Published path: resources/views/vendor/country-data/livewire/geography-select.blade.php

    CSS: plain HTML by default — add your own classes or use the Tailwind-ready
         example in the package README.
--}}
<div
    wire:key="geography-select-{{ $countryField }}"
    dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}"
>

    {{-- ── Country ──────────────────────────────────────────────────────── --}}
    <div class="geography-select__group" style="margin-bottom:1rem">
        <label
            for="gs-country-{{ $countryField }}"
            class="geography-select__label"
        >
            {{ __('Country') }}
            @if($required) <span aria-hidden="true" style="color:red">*</span> @endif
        </label>

        <select
            id="gs-country-{{ $countryField }}"
            wire:model.live="selectedCountry"
            name="{{ $countryField }}"
            class="geography-select__input geography-select__input--country"
            @if($required) required @endif
            aria-label="{{ __('Select Country') }}"
        >
            <option value="">— {{ __('Select Country') }} —</option>

            @foreach($this->countries as $option)
                <option value="{{ $option['value'] }}">
                    {{ $option['flag'] }}  {{ $option['label'] }}
                    @if($option['dial']) ({{ $option['dial'] }}) @endif
                </option>
            @endforeach
        </select>
    </div>

    {{-- ── City (visible after country chosen) ─────────────────────────── --}}
    @if($selectedCountry)
    <div
        class="geography-select__group"
        style="margin-bottom:1rem"
        wire:loading.class="geography-select__group--loading"
        wire:target="selectedCountry"
    >
        <label
            for="gs-city-{{ $cityField }}"
            class="geography-select__label"
        >
            {{ __('City') }}
            @if($required) <span aria-hidden="true" style="color:red">*</span> @endif
        </label>

        {{-- Loading spinner while country changes --}}
        <span
            wire:loading
            wire:target="selectedCountry"
            class="geography-select__loading"
            aria-live="polite"
        >
            ⏳ {{ __('Loading…') }}
        </span>

        <select
            id="gs-city-{{ $cityField }}"
            wire:model.live="selectedCity"
            wire:loading.attr="disabled"
            wire:target="selectedCountry"
            name="{{ $cityField }}"
            class="geography-select__input geography-select__input--city"
            @if($required) required @endif
            aria-label="{{ __('Select City') }}"
        >
            <option value="">— {{ __('Select City') }} —</option>

            @foreach($this->cities as $option)
                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- ── Area (visible after city chosen, if showAreas) ──────────────── --}}
    @if($showAreas && $selectedCity && $this->areas->isNotEmpty())
    <div
        class="geography-select__group"
        wire:loading.class="geography-select__group--loading"
        wire:target="selectedCity"
    >
        <label
            for="gs-area-{{ $areaField }}"
            class="geography-select__label"
        >
            {{ __('Area / District') }}
        </label>

        <span
            wire:loading
            wire:target="selectedCity"
            class="geography-select__loading"
            aria-live="polite"
        >
            ⏳ {{ __('Loading…') }}
        </span>

        <select
            id="gs-area-{{ $areaField }}"
            wire:model.live="selectedArea"
            wire:loading.attr="disabled"
            wire:target="selectedCity"
            name="{{ $areaField }}"
            class="geography-select__input geography-select__input--area"
            aria-label="{{ __('Select Area') }}"
        >
            <option value="">— {{ __('Select Area') }} —</option>

            @foreach($this->areas as $option)
                <option value="{{ $option['value'] }}">
                    {{ $option['label'] }}
                    @if($option['type'] !== 'neighborhood')
                        ({{ __($option['type']) }})
                    @endif
                </option>
            @endforeach
        </select>
    </div>
    @endif

</div>
