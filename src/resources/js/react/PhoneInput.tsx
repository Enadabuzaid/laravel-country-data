/**
 * PhoneInput — searchable dial-code selector + national number input.
 *
 * Part of enadstack/laravel-country-data. Self-contained: depends only on React
 * and Tailwind utility classes (shadcn/ui theme tokens such as `border-input`,
 * `bg-popover`, `ring-ring`). Feed it `Geography::phoneCountriesForSelect()`.
 *
 * The selected country's ISO-2 code is kept in a hidden input (`countryCodeName`)
 * while the user only sees the flag and dial code (e.g. 🇯🇴 +962).
 *
 * @example
 * <PhoneInput
 *     countries={phoneCountries}
 *     countryCode={data.phone_country_code}
 *     phone={data.phone}
 *     defaultCountry="JO"
 *     onChange={({ countryCode, phone }) => setData({ ...data, phone_country_code: countryCode, phone })}
 * />
 */
import { useEffect, useId, useMemo, useRef, useState } from 'react';
import type { KeyboardEvent, ReactNode } from 'react';

export type PhoneCountryOption = {
    /** ISO-2 country code, e.g. `JO`. */
    value: string;
    /** Display name, e.g. `Jordan`. */
    label: string;
    /** Emoji flag, e.g. `🇯🇴`. */
    flag: string | null;
    /** Optional flag image URL (preferred over the emoji when present). */
    flag_svg?: string | null;
    /** International dial code, e.g. `+962`. */
    dial: string;
};

export type PhoneInputValue = {
    /** ISO-2 code of the selected country, or null when none is selected. */
    countryCode: string | null;
    /** Dial code of the selected country, e.g. `+962`. */
    dial: string | null;
    /** National number, digits only, e.g. `772432330`. */
    phone: string;
    /** Full international number without the trunk prefix, e.g. `+962772432330`, or null when empty. */
    e164: string | null;
};

export type PhoneInputProps = {
    countries: PhoneCountryOption[];
    countryCode: string | null | undefined;
    phone: string | null | undefined;
    onChange: (value: PhoneInputValue) => void;
    /** Country used when `countryCode` is empty. */
    defaultCountry?: string | null;
    id?: string;
    /** Name of the hidden ISO-2 input (for native / Inertia `<Form>` submissions). */
    countryCodeName?: string;
    /** Name of the visible phone input. */
    phoneName?: string;
    placeholder?: string;
    searchPlaceholder?: string;
    emptyText?: ReactNode;
    disabled?: boolean;
    required?: boolean;
    invalid?: boolean;
    className?: string;
};

const cx = (...classes: Array<string | false | null | undefined>): string =>
    classes.filter(Boolean).join(' ');

const digitsOnly = (value: string): string => value.replace(/\D+/g, '');

/** Pick the country whose dial code is the longest prefix of an international number. */
function matchDialPrefix(
    countries: PhoneCountryOption[],
    international: string,
): PhoneCountryOption | null {
    const digits = digitsOnly(international);

    return (
        [...countries]
            .sort((a, b) => b.dial.length - a.dial.length)
            .find((country) => digits.startsWith(digitsOnly(country.dial))) ??
        null
    );
}

function Flag({ country }: { country: PhoneCountryOption }) {
    if (country.flag_svg) {
        return (
            <img
                src={country.flag_svg}
                alt=""
                aria-hidden="true"
                className="h-3.5 w-5 shrink-0 rounded-[2px] object-cover shadow-[0_0_0_1px_rgba(0,0,0,0.08)]"
                loading="lazy"
            />
        );
    }

    return (
        <span aria-hidden="true" className="text-base leading-none">
            {country.flag ?? '🏳️'}
        </span>
    );
}

export function PhoneInput({
    countries,
    countryCode,
    phone,
    onChange,
    defaultCountry = null,
    id,
    countryCodeName = 'phone_country_code',
    phoneName = 'phone',
    placeholder = 'Phone number',
    searchPlaceholder = 'Search country or code...',
    emptyText = 'No countries found.',
    disabled = false,
    required = false,
    invalid = false,
    className,
}: PhoneInputProps) {
    const generatedId = useId();
    const inputId = id ?? `phone-${generatedId}`;
    const listId = `${inputId}-countries`;

    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [activeIndex, setActiveIndex] = useState(0);
    const [draft, setDraft] = useState<string | null>(null);

    const containerRef = useRef<HTMLDivElement>(null);
    const searchRef = useRef<HTMLInputElement>(null);
    const phoneRef = useRef<HTMLInputElement>(null);
    const listRef = useRef<HTMLUListElement>(null);

    const effectiveCode = (countryCode || defaultCountry || '').toUpperCase();
    const selected = useMemo(
        () =>
            countries.find((country) => country.value === effectiveCode) ??
            null,
        [countries, effectiveCode],
    );

    const filtered = useMemo(() => {
        const term = query.trim().toLowerCase();

        if (term === '') {
            return countries;
        }

        const termDigits = digitsOnly(term);

        return countries.filter(
            (country) =>
                country.label.toLowerCase().includes(term) ||
                country.value.toLowerCase() === term ||
                (termDigits !== '' &&
                    digitsOnly(country.dial).startsWith(termDigits)),
        );
    }, [countries, query]);

    useEffect(() => {
        if (!open) {
            return;
        }

        const handlePointerDown = (event: PointerEvent) => {
            if (!containerRef.current?.contains(event.target as Node)) {
                setOpen(false);
            }
        };

        document.addEventListener('pointerdown', handlePointerDown);
        searchRef.current?.focus();

        return () =>
            document.removeEventListener('pointerdown', handlePointerDown);
    }, [open]);

    useEffect(() => {
        listRef.current
            ?.querySelector<HTMLElement>(`[data-index="${activeIndex}"]`)
            ?.scrollIntoView({ block: 'nearest' });
    }, [activeIndex]);

    function emit(country: PhoneCountryOption | null, nationalNumber: string) {
        // The national trunk prefix (leading 0) is dropped in international format.
        const significant = nationalNumber.replace(/^0+/, '');

        onChange({
            countryCode: country?.value ?? null,
            dial: country?.dial ?? null,
            phone: nationalNumber,
            e164:
                country && significant !== ''
                    ? `${country.dial}${significant}`
                    : null,
        });
    }

    function openList() {
        if (disabled) {
            return;
        }

        const selectedIndex = selected
            ? countries.findIndex((country) => country.value === selected.value)
            : 0;

        setQuery('');
        setActiveIndex(Math.max(selectedIndex, 0));
        setOpen(true);
    }

    function selectCountry(country: PhoneCountryOption) {
        setDraft(null);
        emit(country, digitsOnly(phone ?? ''));
        setOpen(false);
        phoneRef.current?.focus();
    }

    function handlePhoneChange(raw: string) {
        const trimmed = raw.trim();

        // An international number (+962 7… or 00962 7…) selects its country automatically.
        if (trimmed.startsWith('+') || trimmed.startsWith('00')) {
            const international = trimmed.replace(/^00/, '+');
            const match = matchDialPrefix(countries, international);

            if (!match) {
                // Keep showing what is typed until a complete dial code is recognised.
                setDraft(raw);

                return;
            }

            setDraft(null);
            emit(
                match,
                digitsOnly(international).slice(digitsOnly(match.dial).length),
            );

            return;
        }

        setDraft(null);
        emit(selected, digitsOnly(raw));
    }

    function handleSearchKeyDown(event: KeyboardEvent<HTMLInputElement>) {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActiveIndex((index) =>
                Math.min(index + 1, Math.max(filtered.length - 1, 0)),
            );
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActiveIndex((index) => Math.max(index - 1, 0));
        } else if (event.key === 'Enter') {
            event.preventDefault();
            const country = filtered[activeIndex];

            if (country) {
                selectCountry(country);
            }
        } else if (event.key === 'Escape') {
            event.preventDefault();
            setOpen(false);
        }
    }

    return (
        <div ref={containerRef} className={cx('relative w-full', className)}>
            <input
                type="hidden"
                name={countryCodeName}
                value={selected?.value ?? ''}
            />

            <div
                aria-invalid={invalid || undefined}
                className={cx(
                    'flex h-9 w-full min-w-0 items-stretch rounded-md border border-input bg-transparent shadow-xs transition-[color,box-shadow] dark:bg-input/30',
                    'focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50',
                    invalid &&
                        'border-destructive ring-destructive/20 dark:ring-destructive/40',
                    disabled &&
                        'pointer-events-none cursor-not-allowed opacity-50',
                )}
            >
                <button
                    type="button"
                    onClick={() => (open ? setOpen(false) : openList())}
                    disabled={disabled}
                    aria-haspopup="listbox"
                    aria-expanded={open}
                    aria-controls={listId}
                    aria-label={
                        selected
                            ? `Country: ${selected.label} ${selected.dial}`
                            : 'Select country'
                    }
                    className="flex shrink-0 items-center gap-1.5 rounded-l-md border-r border-input px-2.5 text-sm outline-none hover:bg-accent focus-visible:bg-accent"
                >
                    {selected ? (
                        <>
                            <Flag country={selected} />
                            <span className="font-medium tabular-nums">
                                {selected.dial}
                            </span>
                        </>
                    ) : (
                        <span className="text-muted-foreground">Code</span>
                    )}
                    <svg
                        aria-hidden="true"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        className={cx(
                            'size-3.5 text-muted-foreground transition-transform',
                            open && 'rotate-180',
                        )}
                    >
                        <path
                            fillRule="evenodd"
                            d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z"
                            clipRule="evenodd"
                        />
                    </svg>
                </button>

                <input
                    ref={phoneRef}
                    id={inputId}
                    name={phoneName}
                    type="tel"
                    inputMode="tel"
                    autoComplete="tel-national"
                    value={draft ?? phone ?? ''}
                    onBlur={() => setDraft(null)}
                    onChange={(event) => handlePhoneChange(event.target.value)}
                    placeholder={placeholder}
                    disabled={disabled}
                    required={required}
                    aria-invalid={invalid || undefined}
                    className="w-full min-w-0 rounded-r-md bg-transparent px-3 text-base tabular-nums outline-none placeholder:text-muted-foreground md:text-sm"
                />
            </div>

            {open && (
                <div className="absolute top-full left-0 z-50 mt-1 w-72 max-w-[calc(100vw-2rem)] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md">
                    <div className="flex items-center gap-2 border-b px-3">
                        <svg
                            aria-hidden="true"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            className="size-4 shrink-0 text-muted-foreground"
                        >
                            <path
                                fillRule="evenodd"
                                d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.45 4.39l3.08 3.08a.75.75 0 1 1-1.06 1.06l-3.08-3.08A7 7 0 0 1 2 9Z"
                                clipRule="evenodd"
                            />
                        </svg>
                        <input
                            ref={searchRef}
                            value={query}
                            onChange={(event) => {
                                setQuery(event.target.value);
                                setActiveIndex(0);
                            }}
                            onKeyDown={handleSearchKeyDown}
                            placeholder={searchPlaceholder}
                            aria-label={searchPlaceholder}
                            aria-controls={listId}
                            aria-activedescendant={
                                filtered[activeIndex]
                                    ? `${listId}-${filtered[activeIndex].value}`
                                    : undefined
                            }
                            className="h-9 w-full bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                        />
                    </div>

                    <ul
                        ref={listRef}
                        id={listId}
                        role="listbox"
                        className="max-h-64 overflow-y-auto p-1"
                    >
                        {filtered.length === 0 ? (
                            <li className="px-2 py-6 text-center text-sm text-muted-foreground">
                                {emptyText}
                            </li>
                        ) : (
                            filtered.map((country, index) => (
                                <li
                                    key={country.value}
                                    id={`${listId}-${country.value}`}
                                    data-index={index}
                                    role="option"
                                    aria-selected={
                                        country.value === selected?.value
                                    }
                                    onPointerMove={() => setActiveIndex(index)}
                                    onClick={() => selectCountry(country)}
                                    className={cx(
                                        'flex cursor-pointer items-center gap-2 rounded-sm px-2 py-1.5 text-sm',
                                        index === activeIndex &&
                                            'bg-accent text-accent-foreground',
                                    )}
                                >
                                    <Flag country={country} />
                                    <span className="min-w-0 flex-1 truncate">
                                        {country.label}
                                    </span>
                                    <span className="text-muted-foreground tabular-nums">
                                        {country.dial}
                                    </span>
                                    {country.value === selected?.value && (
                                        <svg
                                            aria-hidden="true"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            className="size-4 text-primary"
                                        >
                                            <path
                                                fillRule="evenodd"
                                                d="M16.7 5.3a1 1 0 0 1 0 1.4l-8 8a1 1 0 0 1-1.4 0l-4-4a1 1 0 1 1 1.4-1.4L8 12.58l7.3-7.3a1 1 0 0 1 1.4 0Z"
                                                clipRule="evenodd"
                                            />
                                        </svg>
                                    )}
                                </li>
                            ))
                        )}
                    </ul>
                </div>
            )}
        </div>
    );
}

export default PhoneInput;
