<?php

namespace Enad\CountryData\Facades;

use Illuminate\Support\Facades\Facade;

class CountryData extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Enad\CountryData\CountryData::class;
    }
}
