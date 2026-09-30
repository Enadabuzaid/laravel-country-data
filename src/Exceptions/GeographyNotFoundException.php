<?php

namespace Enadstack\CountryData\Exceptions;

use InvalidArgumentException;

/**
 * Base for "no such country / region / city" errors. Catch this to handle all three.
 */
abstract class GeographyNotFoundException extends InvalidArgumentException
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $suggestion = null,
        string $message = '',
    ) {
        parent::__construct($message);
    }

    protected static function message(string $kind, string $name, ?string $suggestion): string
    {
        return "{$kind} [{$name}] not found." . ($suggestion !== null ? " Did you mean {$suggestion}?" : '');
    }
}
