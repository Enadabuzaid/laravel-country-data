<?php

namespace Enadstack\CountryData\Exceptions;

class CountryNotFoundException extends GeographyNotFoundException
{
    public static function named(string $name, ?string $suggestion = null): self
    {
        return new self($name, $suggestion, self::message('Country', $name, $suggestion));
    }
}
