<?php

namespace Enadstack\CountryData\Shortcuts;

use Enadstack\CountryData\Enums\CountryCode;
use Enadstack\CountryData\Enums\Region;
use Enadstack\CountryData\Exceptions\CityNotFoundException;
use Enadstack\CountryData\Models\City;
use Enadstack\CountryData\Models\Country;
use Enadstack\CountryData\Support\Lookup;
use Illuminate\Support\Collection;

/**
 * Static, cached shortcuts to cities.
 *
 *   Cities::jordan();                 // cities of Jordan
 *   Cities::of('JO');                 // same, by code / name / CountryCode
 *   Cities::capitalOf('JO');          // City|null
 *   Cities::named('JO', 'Irbid');     // City (throws CityNotFoundException)
 *   Cities::gulf();                   // cities of every country in a region
 *
 * @generated-methods-start
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> arab()  Arab League
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> gulf()  Arabian Gulf
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> gcc()  Gulf Cooperation Council
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> levant()  Levant
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> maghreb()  Maghreb
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> middleEast()  Middle East
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> muslimMajority()  Muslim-majority
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> africa()  Africa
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> asia()  Asia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> europe()  Europe
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> northAmerica()  North America
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> southAmerica()  South America
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> oceania()  Oceania
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> eu()  European Union
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> schengen()  Schengen Area
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> g20()  G20
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> jordan()  JO Jordan
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> saudiArabia()  SA Saudi Arabia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> unitedArabEmirates()  AE United Arab Emirates
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> kuwait()  KW Kuwait
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> qatar()  QA Qatar
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> oman()  OM Oman
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> bahrain()  BH Bahrain
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> iraq()  IQ Iraq
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> syria()  SY Syria
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> lebanon()  LB Lebanon
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> palestine()  PS Palestine
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> egypt()  EG Egypt
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> libya()  LY Libya
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> tunisia()  TN Tunisia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> algeria()  DZ Algeria
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> morocco()  MA Morocco
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> sudan()  SD Sudan
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> yemen()  YE Yemen
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> mauritania()  MR Mauritania
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> somalia()  SO Somalia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> djibouti()  DJ Djibouti
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> comoros()  KM Comoros
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> afghanistan()  AF Afghanistan
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> albania()  AL Albania
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> americanSamoa()  AS American Samoa
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> andorra()  AD Andorra
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> angola()  AO Angola
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> anguilla()  AI Anguilla
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> antarctica()  AQ Antarctica
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> antiguaAndBarbuda()  AG Antigua and Barbuda
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> argentina()  AR Argentina
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> armenia()  AM Armenia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> aruba()  AW Aruba
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> australia()  AU Australia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> austria()  AT Austria
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> azerbaijan()  AZ Azerbaijan
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> bahamas()  BS Bahamas
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> bangladesh()  BD Bangladesh
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> barbados()  BB Barbados
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> belarus()  BY Belarus
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> belgium()  BE Belgium
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> belize()  BZ Belize
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> benin()  BJ Benin
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> bermuda()  BM Bermuda
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> bhutan()  BT Bhutan
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> bolivia()  BO Bolivia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> bosniaAndHerzegovina()  BA Bosnia and Herzegovina
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> botswana()  BW Botswana
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> bouvetIsland()  BV Bouvet Island
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> brazil()  BR Brazil
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> britishIndianOceanTerritory()  IO British Indian Ocean Territory
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> britishVirginIslands()  VG British Virgin Islands
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> brunei()  BN Brunei
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> bulgaria()  BG Bulgaria
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> burkinaFaso()  BF Burkina Faso
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> burundi()  BI Burundi
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> cambodia()  KH Cambodia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> cameroon()  CM Cameroon
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> canada()  CA Canada
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> capeVerde()  CV Cape Verde
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> caribbeanNetherlands()  BQ Caribbean Netherlands
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> caymanIslands()  KY Cayman Islands
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> centralAfricanRepublic()  CF Central African Republic
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> chad()  TD Chad
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> chile()  CL Chile
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> china()  CN China
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> christmasIsland()  CX Christmas Island
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> cocosKeelingIslands()  CC Cocos (Keeling) Islands
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> colombia()  CO Colombia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> congo()  CG Congo
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> cookIslands()  CK Cook Islands
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> costaRica()  CR Costa Rica
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> croatia()  HR Croatia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> cuba()  CU Cuba
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> curacao()  CW Curaçao
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> cyprus()  CY Cyprus
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> czechia()  CZ Czechia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> drCongo()  CD DR Congo
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> denmark()  DK Denmark
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> dominica()  DM Dominica
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> dominicanRepublic()  DO Dominican Republic
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> ecuador()  EC Ecuador
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> elSalvador()  SV El Salvador
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> equatorialGuinea()  GQ Equatorial Guinea
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> eritrea()  ER Eritrea
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> estonia()  EE Estonia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> eswatini()  SZ Eswatini
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> ethiopia()  ET Ethiopia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> falklandIslands()  FK Falkland Islands
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> faroeIslands()  FO Faroe Islands
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> fiji()  FJ Fiji
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> finland()  FI Finland
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> france()  FR France
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> frenchGuiana()  GF French Guiana
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> frenchPolynesia()  PF French Polynesia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> frenchSouthernAndAntarcticLands()  TF French Southern and Antarctic Lands
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> gabon()  GA Gabon
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> gambia()  GM Gambia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> georgia()  GE Georgia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> germany()  DE Germany
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> ghana()  GH Ghana
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> gibraltar()  GI Gibraltar
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> greece()  GR Greece
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> greenland()  GL Greenland
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> grenada()  GD Grenada
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> guadeloupe()  GP Guadeloupe
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> guam()  GU Guam
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> guatemala()  GT Guatemala
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> guernsey()  GG Guernsey
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> guinea()  GN Guinea
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> guineaBissau()  GW Guinea-Bissau
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> guyana()  GY Guyana
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> haiti()  HT Haiti
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> heardIslandAndMcdonaldIslands()  HM Heard Island and McDonald Islands
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> honduras()  HN Honduras
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> hongKong()  HK Hong Kong
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> hungary()  HU Hungary
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> iceland()  IS Iceland
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> india()  IN India
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> indonesia()  ID Indonesia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> iran()  IR Iran
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> ireland()  IE Ireland
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> isleOfMan()  IM Isle of Man
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> israel()  IL Israel
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> italy()  IT Italy
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> ivoryCoast()  CI Ivory Coast
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> jamaica()  JM Jamaica
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> japan()  JP Japan
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> jersey()  JE Jersey
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> kazakhstan()  KZ Kazakhstan
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> kenya()  KE Kenya
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> kiribati()  KI Kiribati
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> kosovo()  XK Kosovo
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> kyrgyzstan()  KG Kyrgyzstan
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> laos()  LA Laos
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> latvia()  LV Latvia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> lesotho()  LS Lesotho
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> liberia()  LR Liberia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> liechtenstein()  LI Liechtenstein
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> lithuania()  LT Lithuania
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> luxembourg()  LU Luxembourg
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> macau()  MO Macau
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> madagascar()  MG Madagascar
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> malawi()  MW Malawi
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> malaysia()  MY Malaysia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> maldives()  MV Maldives
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> mali()  ML Mali
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> malta()  MT Malta
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> marshallIslands()  MH Marshall Islands
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> martinique()  MQ Martinique
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> mauritius()  MU Mauritius
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> mayotte()  YT Mayotte
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> mexico()  MX Mexico
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> micronesia()  FM Micronesia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> moldova()  MD Moldova
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> monaco()  MC Monaco
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> mongolia()  MN Mongolia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> montenegro()  ME Montenegro
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> montserrat()  MS Montserrat
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> mozambique()  MZ Mozambique
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> myanmar()  MM Myanmar
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> namibia()  NA Namibia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> nauru()  NR Nauru
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> nepal()  NP Nepal
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> netherlands()  NL Netherlands
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> newCaledonia()  NC New Caledonia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> newZealand()  NZ New Zealand
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> nicaragua()  NI Nicaragua
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> niger()  NE Niger
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> nigeria()  NG Nigeria
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> niue()  NU Niue
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> norfolkIsland()  NF Norfolk Island
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> northKorea()  KP North Korea
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> northMacedonia()  MK North Macedonia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> northernMarianaIslands()  MP Northern Mariana Islands
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> norway()  NO Norway
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> pakistan()  PK Pakistan
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> palau()  PW Palau
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> panama()  PA Panama
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> papuaNewGuinea()  PG Papua New Guinea
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> paraguay()  PY Paraguay
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> peru()  PE Peru
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> philippines()  PH Philippines
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> pitcairnIslands()  PN Pitcairn Islands
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> poland()  PL Poland
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> portugal()  PT Portugal
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> puertoRico()  PR Puerto Rico
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> romania()  RO Romania
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> russia()  RU Russia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> rwanda()  RW Rwanda
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> reunion()  RE Réunion
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> saintBarthelemy()  BL Saint Barthélemy
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> saintHelenaAscensionAndTristanDaCunha()  SH Saint Helena, Ascension and Tristan da Cunha
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> saintKittsAndNevis()  KN Saint Kitts and Nevis
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> saintLucia()  LC Saint Lucia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> saintMartin()  MF Saint Martin
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> saintPierreAndMiquelon()  PM Saint Pierre and Miquelon
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> saintVincentAndTheGrenadines()  VC Saint Vincent and the Grenadines
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> samoa()  WS Samoa
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> sanMarino()  SM San Marino
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> senegal()  SN Senegal
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> serbia()  RS Serbia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> seychelles()  SC Seychelles
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> sierraLeone()  SL Sierra Leone
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> singapore()  SG Singapore
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> sintMaarten()  SX Sint Maarten
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> slovakia()  SK Slovakia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> slovenia()  SI Slovenia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> solomonIslands()  SB Solomon Islands
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> southAfrica()  ZA South Africa
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> southGeorgia()  GS South Georgia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> southKorea()  KR South Korea
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> southSudan()  SS South Sudan
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> spain()  ES Spain
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> sriLanka()  LK Sri Lanka
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> suriname()  SR Suriname
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> svalbardAndJanMayen()  SJ Svalbard and Jan Mayen
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> sweden()  SE Sweden
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> switzerland()  CH Switzerland
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> saoTomeAndPrincipe()  ST São Tomé and Príncipe
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> taiwan()  TW Taiwan
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> tajikistan()  TJ Tajikistan
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> tanzania()  TZ Tanzania
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> thailand()  TH Thailand
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> timorLeste()  TL Timor-Leste
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> togo()  TG Togo
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> tokelau()  TK Tokelau
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> tonga()  TO Tonga
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> trinidadAndTobago()  TT Trinidad and Tobago
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> turkmenistan()  TM Turkmenistan
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> turksAndCaicosIslands()  TC Turks and Caicos Islands
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> tuvalu()  TV Tuvalu
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> turkiye()  TR Türkiye
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> uganda()  UG Uganda
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> ukraine()  UA Ukraine
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> unitedKingdom()  GB United Kingdom
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> unitedStates()  US United States
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> unitedStatesMinorOutlyingIslands()  UM United States Minor Outlying Islands
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> unitedStatesVirginIslands()  VI United States Virgin Islands
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> uruguay()  UY Uruguay
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> uzbekistan()  UZ Uzbekistan
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> vanuatu()  VU Vanuatu
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> vaticanCity()  VA Vatican City
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> venezuela()  VE Venezuela
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> vietnam()  VN Vietnam
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> wallisAndFutuna()  WF Wallis and Futuna
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> westernSahara()  EH Western Sahara
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> zambia()  ZM Zambia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> zimbabwe()  ZW Zimbabwe
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\City> alandIslands()  AX Åland Islands
 * @generated-methods-end
 */
final class Cities extends Shortcut
{
    /** @return Collection<int, City> */
    public static function of(Country|CountryCode|string $country): Collection
    {
        return static::geo()->cities(static::country($country)->code);
    }

    /** The capital, or null when the country has none (e.g. Antarctica). */
    public static function capitalOf(Country|CountryCode|string $country): ?City
    {
        return static::geo()->capital(static::country($country)->code);
    }

    /** @throws CityNotFoundException with a "did you mean …?" suggestion */
    public static function named(Country|CountryCode|string $country, string $name): City
    {
        $code = static::country($country)->code;

        return static::geo()->city($code, $name)
            ?? static::of($code)->first(fn (City $c) => Lookup::key($c->name_en) === Lookup::key($name))
            ?? throw CityNotFoundException::named($name, Lookup::closest($name, static::of($code)->pluck('name_en')->all()));
    }

    /** @return Collection<int, City> */
    public static function in(Region|string $region): Collection
    {
        return static::geo()->citiesIn($region);
    }

    /** @return Collection<int, City> */
    public static function __callStatic(string $name, array $arguments): Collection
    {
        $target = static::resolve($name);

        return $target instanceof Region ? static::in($target) : static::of($target);
    }
}
