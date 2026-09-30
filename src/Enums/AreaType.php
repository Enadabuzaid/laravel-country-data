<?php

namespace Enadstack\CountryData\Enums;

/**
 * Values of `areas.type`.
 *
 * Districts, zones and governorates are roots of a city's area tree;
 * neighborhoods and streets usually hang off a district (areas.parent_id).
 */
enum AreaType: string
{
    case District     = 'district';
    case Neighborhood = 'neighborhood';
    case Street       = 'street';
    case Zone         = 'zone';
    case Governorate  = 'governorate';
}
