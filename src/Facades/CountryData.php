<?php

namespace Enadstack\CountryData\Facades;

use Illuminate\Support\Facades\Facade;

class CountryData extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Enadstack\CountryData\CountryData::class;
    }
}
