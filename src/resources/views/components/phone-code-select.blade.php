<div class="w-full">
    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 dark:text-white mb-1">
        {{ $label ?? 'Phone Number' }}
    </label>
    <div class="flex gap-2">
        <select name="{{ $codeName ?? 'dial_code' }}" class="w-1/3 px-4 py-2 border rounded-lg dark:bg-gray-800 dark:text-white">
            @foreach($countries as $country)
                <option value="{{ $country['dial'] }}">
                    {{ $withFlag ? $country['flag'] . ' ' : '' }}{{ $country['dial'] }}
                </option>
            @endforeach
        </select>

        <input
                type="tel"
                name="{{ $inputName ?? 'phone_number' }}"
                class="w-2/3 px-4 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:text-white"
                placeholder="Enter phone number"
        />
    </div>
</div>
