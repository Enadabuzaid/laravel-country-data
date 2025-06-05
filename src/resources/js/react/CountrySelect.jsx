import React, { useState } from 'react';

export default function CountrySelect({
                                          countries = [],
                                          value,
                                          onChange,
                                          preferred = [],
                                          withFlag = true,
                                          placeholder = 'Search country...',
                                          rtl = false,
                                          label = 'Select Country'
                                      }) {
    const [search, setSearch] = useState('');

    const ordered = [...preferred, ...countries.filter(c => !preferred.includes(c.code))];
    const filtered = ordered.filter(country => {
        const name = rtl ? country.name.ar : country.name.en;
        return name.toLowerCase().includes(search.toLowerCase());
    });

    return (
        <div className="w-full">
            <label className="block text-sm font-medium text-gray-700 dark:text-white mb-1">{label}</label>
            <input
                type="text"
                className="w-full px-4 py-2 border border-gray-300 rounded-lg mb-1 dark:bg-gray-800 dark:text-white"
                placeholder={placeholder}
                value={search}
                onChange={e => setSearch(e.target.value)}
            />
            <ul className="border rounded-lg max-h-60 overflow-auto shadow-md dark:bg-gray-900">
                {filtered.map((country) => (
                    <li
                        key={country.code}
                        onClick={() => onChange(country)}
                        className="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 cursor-pointer"
                    >
                        {withFlag && <span>{country.flag}</span>}
                        <span dir={rtl ? 'rtl' : 'ltr'}>
              {rtl ? country.name.ar : country.name.en}
            </span>
                    </li>
                ))}
            </ul>
        </div>
    );
}