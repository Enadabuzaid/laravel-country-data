<?php

/** Accent-, case- and punctuation-insensitive form of a place name. */
function normalizeName(string $s): string
{
    $s = \Normalizer::normalize($s, \Normalizer::FORM_D) ?: $s;
    $s = preg_replace('/\p{Mn}+/u', '', $s);

    return preg_replace('/[^a-z]/', '', strtolower($s));
}

function namesMatch(string $a, string $b): bool
{
    $a = normalizeName($a);
    $b = normalizeName($b);

    return $a !== '' && $b !== '' && ($a === $b || str_contains($a, $b) || str_contains($b, $a));
}

/** Great-circle distance in km. */
function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $a = sin(deg2rad($lat2 - $lat1) / 2) ** 2
       + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin(deg2rad($lng2 - $lng1) / 2) ** 2;

    return 6371 * 2 * asin(sqrt($a));
}
