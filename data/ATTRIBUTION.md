# Data sources and licences

The files in this directory are built by `scripts/build-data.php` from the
curated data in `resources/curated/` and the pinned upstream copies in
`resources/source/` (see `resources/source/manifest.json` for exact revisions).

| Data | Source | Licence |
|---|---|---|
| The 22 Arab League countries, their 136 cities and all areas | Hand-curated for this package | MIT (package licence) |
| Country names, codes, currencies, dial codes, borders, languages, TLDs, area, coordinates | [mledoze/countries](https://github.com/mledoze/countries) | [ODbL 1.0](https://opendatacommons.org/licenses/odbl/1-0/) |
| Arabic country and currency names | [Unicode CLDR](https://github.com/unicode-org/cldr-json), `ar` locale | [Unicode License v3](https://www.unicode.org/license.txt) |
| Continent / region / sub-region | [UN M49](https://unstats.un.org/unsd/methodology/m49/) | UN Statistics Division |
| Time zones | [IANA tz database](https://www.iana.org/time-zones) `zone.tab` | Public domain |
| Population, capitals (coordinates, Arabic names), coat of arms, OpenStreetMap relations | [Wikidata](https://www.wikidata.org/) | [CC0 1.0](https://creativecommons.org/publicdomain/zero/1.0/) |
| Language code mapping | [SIL ISO 639-3](https://iso639-3.sil.org/) | SIL terms of use |

`countries.json` and `cities.json` contain data derived from mledoze/countries
and are therefore made available under the Open Database License (ODbL) 1.0:
you may use, share and adapt them, but must attribute the source and keep any
public adaptation of the *database* under the ODbL. This applies to the data
files only, not to the package's PHP code, which stays MIT.
