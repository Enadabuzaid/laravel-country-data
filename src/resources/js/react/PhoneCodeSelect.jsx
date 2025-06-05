import React, { useState } from 'react';

export default function PhoneCodeSelect({ countries = [], withFlag = true, label = 'Phone Number', onChange }) {
    const [code, setCode] = useState('');
    const [number, setNumber] = useState('');

    const emit = (c, n) => onChange && onChange({ dial: c, number: n });

    return (
        <div className="w-full">
            <label className="block text-sm font-medium text-gray-700 dark:text-white mb-1">{label}</label>
            <div className="flex gap-2">
                <select
                    className="w-1/3 px-4 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:text-white"
                    value={code}
                    onChange={e => {
                        setCode(e.target.value);
                        emit(e.target.value, number);
                    }}
                >
                    {countries.map(country => (
                        <option key={country.code} value={country.dial}>
                            {withFlag && country.flag + ' '}{country.dial}
                        </option>
                    ))}
                </select>

                <input
                    type="tel"
                    placeholder="Enter phone number"
                    className="w-2/3 px-4 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:text-white"
                    value={number}
                    onChange={e => {
                        setNumber(e.target.value);
                        emit(code, e.target.value);
                    }}
                />
            </div>
        </div>
    );
}