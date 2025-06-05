<div class="w-full">
    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 dark:text-white mb-1">
        {{ $label ?? 'Select Country' }}
    </label>
    <select
            id="{{ $id }}"
            name="{{ $name }}"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:text-white"
    >
        @if($preferred && is_array($preferred))
            @foreach($countries->whereIn('code', $preferred) as $country)
                <option value="{{ $country['code'] }}">
                    {{ $withFlag ? $country['flag'] . ' ' : '' }}{{ $rtl ? $country['name']['ar'] : $country['name']['en'] }}
                </option>
            @endforeach
            <option disabled>──────────────</option>
        @endif

        @foreach($countries->whereNotIn('code', $preferred ?? []) as $country)
            <option value="{{ $country['code'] }}">
                {{ $withFlag ? $country['flag'] . ' ' : '' }}{{ $rtl ? $country['name']['ar'] : $country['name']['en'] }}
            </option>
        @endforeach
    </select>
</div>