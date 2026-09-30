<?php

namespace Enadstack\CountryData\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @deprecated 3.0 Use the Countries shortcut or the Geography facade; see UPGRADE.md.
 */
class CountryData extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Enadstack\CountryData\CountryData::class;
    }
}
