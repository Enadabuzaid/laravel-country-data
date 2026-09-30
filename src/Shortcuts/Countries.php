<?php

namespace Enadstack\CountryData\Shortcuts;

use Enadstack\CountryData\Enums\CountryCode;
use Enadstack\CountryData\Enums\Region;
use Enadstack\CountryData\Models\Country;
use Illuminate\Support\Collection;

/**
 * Static, cached shortcuts to countries.
 *
 *   Countries::jordan();              // Country (by name, slug or ISO code)
 *   Countries::of('JO');              // ISO-2, ISO-3, CountryCode or name
 *   Countries::europe();              // Collection — any Region name works
 *   Countries::in(Region::Levant);
 *   Countries::all();
 *
 * Unknown names throw CountryNotFoundException / RegionNotFoundException with
 * a "did you mean …?" suggestion.
 *
 * @generated-methods-start
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\Country> arab()  Arab League
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\Country> gulf()  Arabian Gulf
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\Country> gcc()  Gulf Cooperation Council
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\Country> levant()  Levant
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\Country> maghreb()  Maghreb
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\Country> middleEast()  Middle East
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\Country> muslimMajority()  Muslim-majority
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\Country> africa()  Africa
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\Country> asia()  Asia
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\Country> europe()  Europe
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\Country> northAmerica()  North America
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\Country> southAmerica()  South America
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\Country> oceania()  Oceania
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\Country> eu()  European Union
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\Country> schengen()  Schengen Area
 * @method static \Illuminate\Support\Collection<int, \Enadstack\CountryData\Models\Country> g20()  G20
 * @method static \Enadstack\CountryData\Models\Country jordan()  JO Jordan
 * @method static \Enadstack\CountryData\Models\Country saudiArabia()  SA Saudi Arabia
 * @method static \Enadstack\CountryData\Models\Country unitedArabEmirates()  AE United Arab Emirates
 * @method static \Enadstack\CountryData\Models\Country kuwait()  KW Kuwait
 * @method static \Enadstack\CountryData\Models\Country qatar()  QA Qatar
 * @method static \Enadstack\CountryData\Models\Country oman()  OM Oman
 * @method static \Enadstack\CountryData\Models\Country bahrain()  BH Bahrain
 * @method static \Enadstack\CountryData\Models\Country iraq()  IQ Iraq
 * @method static \Enadstack\CountryData\Models\Country syria()  SY Syria
 * @method static \Enadstack\CountryData\Models\Country lebanon()  LB Lebanon
 * @method static \Enadstack\CountryData\Models\Country palestine()  PS Palestine
 * @method static \Enadstack\CountryData\Models\Country egypt()  EG Egypt
 * @method static \Enadstack\CountryData\Models\Country libya()  LY Libya
 * @method static \Enadstack\CountryData\Models\Country tunisia()  TN Tunisia
 * @method static \Enadstack\CountryData\Models\Country algeria()  DZ Algeria
 * @method static \Enadstack\CountryData\Models\Country morocco()  MA Morocco
 * @method static \Enadstack\CountryData\Models\Country sudan()  SD Sudan
 * @method static \Enadstack\CountryData\Models\Country yemen()  YE Yemen
 * @method static \Enadstack\CountryData\Models\Country mauritania()  MR Mauritania
 * @method static \Enadstack\CountryData\Models\Country somalia()  SO Somalia
 * @method static \Enadstack\CountryData\Models\Country djibouti()  DJ Djibouti
 * @method static \Enadstack\CountryData\Models\Country comoros()  KM Comoros
 * @method static \Enadstack\CountryData\Models\Country afghanistan()  AF Afghanistan
 * @method static \Enadstack\CountryData\Models\Country albania()  AL Albania
 * @method static \Enadstack\CountryData\Models\Country americanSamoa()  AS American Samoa
 * @method static \Enadstack\CountryData\Models\Country andorra()  AD Andorra
 * @method static \Enadstack\CountryData\Models\Country angola()  AO Angola
 * @method static \Enadstack\CountryData\Models\Country anguilla()  AI Anguilla
 * @method static \Enadstack\CountryData\Models\Country antarctica()  AQ Antarctica
 * @method static \Enadstack\CountryData\Models\Country antiguaAndBarbuda()  AG Antigua and Barbuda
 * @method static \Enadstack\CountryData\Models\Country argentina()  AR Argentina
 * @method static \Enadstack\CountryData\Models\Country armenia()  AM Armenia
 * @method static \Enadstack\CountryData\Models\Country aruba()  AW Aruba
 * @method static \Enadstack\CountryData\Models\Country australia()  AU Australia
 * @method static \Enadstack\CountryData\Models\Country austria()  AT Austria
 * @method static \Enadstack\CountryData\Models\Country azerbaijan()  AZ Azerbaijan
 * @method static \Enadstack\CountryData\Models\Country bahamas()  BS Bahamas
 * @method static \Enadstack\CountryData\Models\Country bangladesh()  BD Bangladesh
 * @method static \Enadstack\CountryData\Models\Country barbados()  BB Barbados
 * @method static \Enadstack\CountryData\Models\Country belarus()  BY Belarus
 * @method static \Enadstack\CountryData\Models\Country belgium()  BE Belgium
 * @method static \Enadstack\CountryData\Models\Country belize()  BZ Belize
 * @method static \Enadstack\CountryData\Models\Country benin()  BJ Benin
 * @method static \Enadstack\CountryData\Models\Country bermuda()  BM Bermuda
 * @method static \Enadstack\CountryData\Models\Country bhutan()  BT Bhutan
 * @method static \Enadstack\CountryData\Models\Country bolivia()  BO Bolivia
 * @method static \Enadstack\CountryData\Models\Country bosniaAndHerzegovina()  BA Bosnia and Herzegovina
 * @method static \Enadstack\CountryData\Models\Country botswana()  BW Botswana
 * @method static \Enadstack\CountryData\Models\Country bouvetIsland()  BV Bouvet Island
 * @method static \Enadstack\CountryData\Models\Country brazil()  BR Brazil
 * @method static \Enadstack\CountryData\Models\Country britishIndianOceanTerritory()  IO British Indian Ocean Territory
 * @method static \Enadstack\CountryData\Models\Country britishVirginIslands()  VG British Virgin Islands
 * @method static \Enadstack\CountryData\Models\Country brunei()  BN Brunei
 * @method static \Enadstack\CountryData\Models\Country bulgaria()  BG Bulgaria
 * @method static \Enadstack\CountryData\Models\Country burkinaFaso()  BF Burkina Faso
 * @method static \Enadstack\CountryData\Models\Country burundi()  BI Burundi
 * @method static \Enadstack\CountryData\Models\Country cambodia()  KH Cambodia
 * @method static \Enadstack\CountryData\Models\Country cameroon()  CM Cameroon
 * @method static \Enadstack\CountryData\Models\Country canada()  CA Canada
 * @method static \Enadstack\CountryData\Models\Country capeVerde()  CV Cape Verde
 * @method static \Enadstack\CountryData\Models\Country caribbeanNetherlands()  BQ Caribbean Netherlands
 * @method static \Enadstack\CountryData\Models\Country caymanIslands()  KY Cayman Islands
 * @method static \Enadstack\CountryData\Models\Country centralAfricanRepublic()  CF Central African Republic
 * @method static \Enadstack\CountryData\Models\Country chad()  TD Chad
 * @method static \Enadstack\CountryData\Models\Country chile()  CL Chile
 * @method static \Enadstack\CountryData\Models\Country china()  CN China
 * @method static \Enadstack\CountryData\Models\Country christmasIsland()  CX Christmas Island
 * @method static \Enadstack\CountryData\Models\Country cocosKeelingIslands()  CC Cocos (Keeling) Islands
 * @method static \Enadstack\CountryData\Models\Country colombia()  CO Colombia
 * @method static \Enadstack\CountryData\Models\Country congo()  CG Congo
 * @method static \Enadstack\CountryData\Models\Country cookIslands()  CK Cook Islands
 * @method static \Enadstack\CountryData\Models\Country costaRica()  CR Costa Rica
 * @method static \Enadstack\CountryData\Models\Country croatia()  HR Croatia
 * @method static \Enadstack\CountryData\Models\Country cuba()  CU Cuba
 * @method static \Enadstack\CountryData\Models\Country curacao()  CW Curaçao
 * @method static \Enadstack\CountryData\Models\Country cyprus()  CY Cyprus
 * @method static \Enadstack\CountryData\Models\Country czechia()  CZ Czechia
 * @method static \Enadstack\CountryData\Models\Country drCongo()  CD DR Congo
 * @method static \Enadstack\CountryData\Models\Country denmark()  DK Denmark
 * @method static \Enadstack\CountryData\Models\Country dominica()  DM Dominica
 * @method static \Enadstack\CountryData\Models\Country dominicanRepublic()  DO Dominican Republic
 * @method static \Enadstack\CountryData\Models\Country ecuador()  EC Ecuador
 * @method static \Enadstack\CountryData\Models\Country elSalvador()  SV El Salvador
 * @method static \Enadstack\CountryData\Models\Country equatorialGuinea()  GQ Equatorial Guinea
 * @method static \Enadstack\CountryData\Models\Country eritrea()  ER Eritrea
 * @method static \Enadstack\CountryData\Models\Country estonia()  EE Estonia
 * @method static \Enadstack\CountryData\Models\Country eswatini()  SZ Eswatini
 * @method static \Enadstack\CountryData\Models\Country ethiopia()  ET Ethiopia
 * @method static \Enadstack\CountryData\Models\Country falklandIslands()  FK Falkland Islands
 * @method static \Enadstack\CountryData\Models\Country faroeIslands()  FO Faroe Islands
 * @method static \Enadstack\CountryData\Models\Country fiji()  FJ Fiji
 * @method static \Enadstack\CountryData\Models\Country finland()  FI Finland
 * @method static \Enadstack\CountryData\Models\Country france()  FR France
 * @method static \Enadstack\CountryData\Models\Country frenchGuiana()  GF French Guiana
 * @method static \Enadstack\CountryData\Models\Country frenchPolynesia()  PF French Polynesia
 * @method static \Enadstack\CountryData\Models\Country frenchSouthernAndAntarcticLands()  TF French Southern and Antarctic Lands
 * @method static \Enadstack\CountryData\Models\Country gabon()  GA Gabon
 * @method static \Enadstack\CountryData\Models\Country gambia()  GM Gambia
 * @method static \Enadstack\CountryData\Models\Country georgia()  GE Georgia
 * @method static \Enadstack\CountryData\Models\Country germany()  DE Germany
 * @method static \Enadstack\CountryData\Models\Country ghana()  GH Ghana
 * @method static \Enadstack\CountryData\Models\Country gibraltar()  GI Gibraltar
 * @method static \Enadstack\CountryData\Models\Country greece()  GR Greece
 * @method static \Enadstack\CountryData\Models\Country greenland()  GL Greenland
 * @method static \Enadstack\CountryData\Models\Country grenada()  GD Grenada
 * @method static \Enadstack\CountryData\Models\Country guadeloupe()  GP Guadeloupe
 * @method static \Enadstack\CountryData\Models\Country guam()  GU Guam
 * @method static \Enadstack\CountryData\Models\Country guatemala()  GT Guatemala
 * @method static \Enadstack\CountryData\Models\Country guernsey()  GG Guernsey
 * @method static \Enadstack\CountryData\Models\Country guinea()  GN Guinea
 * @method static \Enadstack\CountryData\Models\Country guineaBissau()  GW Guinea-Bissau
 * @method static \Enadstack\CountryData\Models\Country guyana()  GY Guyana
 * @method static \Enadstack\CountryData\Models\Country haiti()  HT Haiti
 * @method static \Enadstack\CountryData\Models\Country heardIslandAndMcdonaldIslands()  HM Heard Island and McDonald Islands
 * @method static \Enadstack\CountryData\Models\Country honduras()  HN Honduras
 * @method static \Enadstack\CountryData\Models\Country hongKong()  HK Hong Kong
 * @method static \Enadstack\CountryData\Models\Country hungary()  HU Hungary
 * @method static \Enadstack\CountryData\Models\Country iceland()  IS Iceland
 * @method static \Enadstack\CountryData\Models\Country india()  IN India
 * @method static \Enadstack\CountryData\Models\Country indonesia()  ID Indonesia
 * @method static \Enadstack\CountryData\Models\Country iran()  IR Iran
 * @method static \Enadstack\CountryData\Models\Country ireland()  IE Ireland
 * @method static \Enadstack\CountryData\Models\Country isleOfMan()  IM Isle of Man
 * @method static \Enadstack\CountryData\Models\Country israel()  IL Israel
 * @method static \Enadstack\CountryData\Models\Country italy()  IT Italy
 * @method static \Enadstack\CountryData\Models\Country ivoryCoast()  CI Ivory Coast
 * @method static \Enadstack\CountryData\Models\Country jamaica()  JM Jamaica
 * @method static \Enadstack\CountryData\Models\Country japan()  JP Japan
 * @method static \Enadstack\CountryData\Models\Country jersey()  JE Jersey
 * @method static \Enadstack\CountryData\Models\Country kazakhstan()  KZ Kazakhstan
 * @method static \Enadstack\CountryData\Models\Country kenya()  KE Kenya
 * @method static \Enadstack\CountryData\Models\Country kiribati()  KI Kiribati
 * @method static \Enadstack\CountryData\Models\Country kosovo()  XK Kosovo
 * @method static \Enadstack\CountryData\Models\Country kyrgyzstan()  KG Kyrgyzstan
 * @method static \Enadstack\CountryData\Models\Country laos()  LA Laos
 * @method static \Enadstack\CountryData\Models\Country latvia()  LV Latvia
 * @method static \Enadstack\CountryData\Models\Country lesotho()  LS Lesotho
 * @method static \Enadstack\CountryData\Models\Country liberia()  LR Liberia
 * @method static \Enadstack\CountryData\Models\Country liechtenstein()  LI Liechtenstein
 * @method static \Enadstack\CountryData\Models\Country lithuania()  LT Lithuania
 * @method static \Enadstack\CountryData\Models\Country luxembourg()  LU Luxembourg
 * @method static \Enadstack\CountryData\Models\Country macau()  MO Macau
 * @method static \Enadstack\CountryData\Models\Country madagascar()  MG Madagascar
 * @method static \Enadstack\CountryData\Models\Country malawi()  MW Malawi
 * @method static \Enadstack\CountryData\Models\Country malaysia()  MY Malaysia
 * @method static \Enadstack\CountryData\Models\Country maldives()  MV Maldives
 * @method static \Enadstack\CountryData\Models\Country mali()  ML Mali
 * @method static \Enadstack\CountryData\Models\Country malta()  MT Malta
 * @method static \Enadstack\CountryData\Models\Country marshallIslands()  MH Marshall Islands
 * @method static \Enadstack\CountryData\Models\Country martinique()  MQ Martinique
 * @method static \Enadstack\CountryData\Models\Country mauritius()  MU Mauritius
 * @method static \Enadstack\CountryData\Models\Country mayotte()  YT Mayotte
 * @method static \Enadstack\CountryData\Models\Country mexico()  MX Mexico
 * @method static \Enadstack\CountryData\Models\Country micronesia()  FM Micronesia
 * @method static \Enadstack\CountryData\Models\Country moldova()  MD Moldova
 * @method static \Enadstack\CountryData\Models\Country monaco()  MC Monaco
 * @method static \Enadstack\CountryData\Models\Country mongolia()  MN Mongolia
 * @method static \Enadstack\CountryData\Models\Country montenegro()  ME Montenegro
 * @method static \Enadstack\CountryData\Models\Country montserrat()  MS Montserrat
 * @method static \Enadstack\CountryData\Models\Country mozambique()  MZ Mozambique
 * @method static \Enadstack\CountryData\Models\Country myanmar()  MM Myanmar
 * @method static \Enadstack\CountryData\Models\Country namibia()  NA Namibia
 * @method static \Enadstack\CountryData\Models\Country nauru()  NR Nauru
 * @method static \Enadstack\CountryData\Models\Country nepal()  NP Nepal
 * @method static \Enadstack\CountryData\Models\Country netherlands()  NL Netherlands
 * @method static \Enadstack\CountryData\Models\Country newCaledonia()  NC New Caledonia
 * @method static \Enadstack\CountryData\Models\Country newZealand()  NZ New Zealand
 * @method static \Enadstack\CountryData\Models\Country nicaragua()  NI Nicaragua
 * @method static \Enadstack\CountryData\Models\Country niger()  NE Niger
 * @method static \Enadstack\CountryData\Models\Country nigeria()  NG Nigeria
 * @method static \Enadstack\CountryData\Models\Country niue()  NU Niue
 * @method static \Enadstack\CountryData\Models\Country norfolkIsland()  NF Norfolk Island
 * @method static \Enadstack\CountryData\Models\Country northKorea()  KP North Korea
 * @method static \Enadstack\CountryData\Models\Country northMacedonia()  MK North Macedonia
 * @method static \Enadstack\CountryData\Models\Country northernMarianaIslands()  MP Northern Mariana Islands
 * @method static \Enadstack\CountryData\Models\Country norway()  NO Norway
 * @method static \Enadstack\CountryData\Models\Country pakistan()  PK Pakistan
 * @method static \Enadstack\CountryData\Models\Country palau()  PW Palau
 * @method static \Enadstack\CountryData\Models\Country panama()  PA Panama
 * @method static \Enadstack\CountryData\Models\Country papuaNewGuinea()  PG Papua New Guinea
 * @method static \Enadstack\CountryData\Models\Country paraguay()  PY Paraguay
 * @method static \Enadstack\CountryData\Models\Country peru()  PE Peru
 * @method static \Enadstack\CountryData\Models\Country philippines()  PH Philippines
 * @method static \Enadstack\CountryData\Models\Country pitcairnIslands()  PN Pitcairn Islands
 * @method static \Enadstack\CountryData\Models\Country poland()  PL Poland
 * @method static \Enadstack\CountryData\Models\Country portugal()  PT Portugal
 * @method static \Enadstack\CountryData\Models\Country puertoRico()  PR Puerto Rico
 * @method static \Enadstack\CountryData\Models\Country romania()  RO Romania
 * @method static \Enadstack\CountryData\Models\Country russia()  RU Russia
 * @method static \Enadstack\CountryData\Models\Country rwanda()  RW Rwanda
 * @method static \Enadstack\CountryData\Models\Country reunion()  RE Réunion
 * @method static \Enadstack\CountryData\Models\Country saintBarthelemy()  BL Saint Barthélemy
 * @method static \Enadstack\CountryData\Models\Country saintHelenaAscensionAndTristanDaCunha()  SH Saint Helena, Ascension and Tristan da Cunha
 * @method static \Enadstack\CountryData\Models\Country saintKittsAndNevis()  KN Saint Kitts and Nevis
 * @method static \Enadstack\CountryData\Models\Country saintLucia()  LC Saint Lucia
 * @method static \Enadstack\CountryData\Models\Country saintMartin()  MF Saint Martin
 * @method static \Enadstack\CountryData\Models\Country saintPierreAndMiquelon()  PM Saint Pierre and Miquelon
 * @method static \Enadstack\CountryData\Models\Country saintVincentAndTheGrenadines()  VC Saint Vincent and the Grenadines
 * @method static \Enadstack\CountryData\Models\Country samoa()  WS Samoa
 * @method static \Enadstack\CountryData\Models\Country sanMarino()  SM San Marino
 * @method static \Enadstack\CountryData\Models\Country senegal()  SN Senegal
 * @method static \Enadstack\CountryData\Models\Country serbia()  RS Serbia
 * @method static \Enadstack\CountryData\Models\Country seychelles()  SC Seychelles
 * @method static \Enadstack\CountryData\Models\Country sierraLeone()  SL Sierra Leone
 * @method static \Enadstack\CountryData\Models\Country singapore()  SG Singapore
 * @method static \Enadstack\CountryData\Models\Country sintMaarten()  SX Sint Maarten
 * @method static \Enadstack\CountryData\Models\Country slovakia()  SK Slovakia
 * @method static \Enadstack\CountryData\Models\Country slovenia()  SI Slovenia
 * @method static \Enadstack\CountryData\Models\Country solomonIslands()  SB Solomon Islands
 * @method static \Enadstack\CountryData\Models\Country southAfrica()  ZA South Africa
 * @method static \Enadstack\CountryData\Models\Country southGeorgia()  GS South Georgia
 * @method static \Enadstack\CountryData\Models\Country southKorea()  KR South Korea
 * @method static \Enadstack\CountryData\Models\Country southSudan()  SS South Sudan
 * @method static \Enadstack\CountryData\Models\Country spain()  ES Spain
 * @method static \Enadstack\CountryData\Models\Country sriLanka()  LK Sri Lanka
 * @method static \Enadstack\CountryData\Models\Country suriname()  SR Suriname
 * @method static \Enadstack\CountryData\Models\Country svalbardAndJanMayen()  SJ Svalbard and Jan Mayen
 * @method static \Enadstack\CountryData\Models\Country sweden()  SE Sweden
 * @method static \Enadstack\CountryData\Models\Country switzerland()  CH Switzerland
 * @method static \Enadstack\CountryData\Models\Country saoTomeAndPrincipe()  ST São Tomé and Príncipe
 * @method static \Enadstack\CountryData\Models\Country taiwan()  TW Taiwan
 * @method static \Enadstack\CountryData\Models\Country tajikistan()  TJ Tajikistan
 * @method static \Enadstack\CountryData\Models\Country tanzania()  TZ Tanzania
 * @method static \Enadstack\CountryData\Models\Country thailand()  TH Thailand
 * @method static \Enadstack\CountryData\Models\Country timorLeste()  TL Timor-Leste
 * @method static \Enadstack\CountryData\Models\Country togo()  TG Togo
 * @method static \Enadstack\CountryData\Models\Country tokelau()  TK Tokelau
 * @method static \Enadstack\CountryData\Models\Country tonga()  TO Tonga
 * @method static \Enadstack\CountryData\Models\Country trinidadAndTobago()  TT Trinidad and Tobago
 * @method static \Enadstack\CountryData\Models\Country turkmenistan()  TM Turkmenistan
 * @method static \Enadstack\CountryData\Models\Country turksAndCaicosIslands()  TC Turks and Caicos Islands
 * @method static \Enadstack\CountryData\Models\Country tuvalu()  TV Tuvalu
 * @method static \Enadstack\CountryData\Models\Country turkiye()  TR Türkiye
 * @method static \Enadstack\CountryData\Models\Country uganda()  UG Uganda
 * @method static \Enadstack\CountryData\Models\Country ukraine()  UA Ukraine
 * @method static \Enadstack\CountryData\Models\Country unitedKingdom()  GB United Kingdom
 * @method static \Enadstack\CountryData\Models\Country unitedStates()  US United States
 * @method static \Enadstack\CountryData\Models\Country unitedStatesMinorOutlyingIslands()  UM United States Minor Outlying Islands
 * @method static \Enadstack\CountryData\Models\Country unitedStatesVirginIslands()  VI United States Virgin Islands
 * @method static \Enadstack\CountryData\Models\Country uruguay()  UY Uruguay
 * @method static \Enadstack\CountryData\Models\Country uzbekistan()  UZ Uzbekistan
 * @method static \Enadstack\CountryData\Models\Country vanuatu()  VU Vanuatu
 * @method static \Enadstack\CountryData\Models\Country vaticanCity()  VA Vatican City
 * @method static \Enadstack\CountryData\Models\Country venezuela()  VE Venezuela
 * @method static \Enadstack\CountryData\Models\Country vietnam()  VN Vietnam
 * @method static \Enadstack\CountryData\Models\Country wallisAndFutuna()  WF Wallis and Futuna
 * @method static \Enadstack\CountryData\Models\Country westernSahara()  EH Western Sahara
 * @method static \Enadstack\CountryData\Models\Country zambia()  ZM Zambia
 * @method static \Enadstack\CountryData\Models\Country zimbabwe()  ZW Zimbabwe
 * @method static \Enadstack\CountryData\Models\Country alandIslands()  AX Åland Islands
 * @generated-methods-end
 */
final class Countries extends Shortcut
{
    /** @return Collection<int, Country> */
    public static function all(): Collection
    {
        return static::geo()->countries();
    }

    /** @throws \Enadstack\CountryData\Exceptions\CountryNotFoundException */
    public static function of(Country|CountryCode|string $country): Country
    {
        return static::country($country);
    }

    /** @return Collection<int, Country> */
    public static function in(Region|string $region): Collection
    {
        return static::geo()->countriesIn($region);
    }

    /** @return Country|Collection<int, Country> */
    public static function __callStatic(string $name, array $arguments): Country|Collection
    {
        $target = static::resolve($name);

        return $target instanceof Region ? static::in($target) : $target;
    }
}
