<?php

namespace Enadstack\CountryData\Support;

use Illuminate\Support\Str;

/**
 * Name normalisation shared by the shortcut classes, the enums and the data
 * build (scripts/build-data.php), so a name resolves the same way everywhere.
 */
final class Lookup
{
    /**
     * Comparison key: case-, accent-, space- and punctuation-insensitive.
     * "Saudi Arabia", "saudi-arabia", "saudiArabia" and "SAUDI_ARABIA" all
     * give "saudiarabia". Arabic keeps its letters ("الأردن" → "الأردن").
     */
    public static function key(string $name): string
    {
        if (preg_match('/\p{Arabic}/u', $name)) {
            return preg_replace('/[^\p{L}\p{N}]+/u', '', mb_strtolower($name));
        }

        return preg_replace('/[^a-z0-9]+/', '', strtolower(Str::ascii($name)));
    }

    /**
     * The static-method spelling of a name: "Saudi Arabia" → saudiArabia,
     * "Côte d'Ivoire" → coteDivoire, "north-america" → northAmerica.
     */
    public static function method(string $name): string
    {
        $ascii = str_replace(["'", '’', '`'], '', Str::ascii($name));
        $words = preg_split('/[^A-Za-z0-9]+/', $ascii, -1, PREG_SPLIT_NO_EMPTY);

        return lcfirst(implode('', array_map(fn ($w) => ucfirst(strtolower($w)), $words)));
    }

    /**
     * The candidate closest to $needle by Levenshtein distance on their keys,
     * or null when nothing is close enough to be a plausible typo.
     *
     * @param  iterable<string>  $candidates
     */
    public static function closest(string $needle, iterable $candidates): ?string
    {
        $key       = self::key($needle);
        $threshold = max(2, intdiv(strlen($key), 3));
        $best      = null;
        $bestScore = PHP_INT_MAX;

        foreach ($candidates as $candidate) {
            $score = levenshtein($key, self::key($candidate));

            if ($score < $bestScore) {
                [$best, $bestScore] = [$candidate, $score];
            }
        }

        return $bestScore <= $threshold ? $best : null;
    }
}
