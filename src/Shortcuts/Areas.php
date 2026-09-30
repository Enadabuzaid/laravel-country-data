<?php

namespace Enadstack\CountryData\Shortcuts;

use Enadstack\CountryData\Enums\CountryCode;
use Enadstack\CountryData\Enums\Region;
use Enadstack\CountryData\Models\City;
use Enadstack\CountryData\Models\Country;
use Enadstack\CountryData\Support\AreaCollection;

/**
 * Static, cached shortcuts to areas. Every result is an AreaCollection, so it
 * can be narrowed without another query.
 *
 *   Areas::jordan();                          // all areas in Jordan
 *   Areas::in('JO', 'Amman');                 // one city
 *   Areas::in('JO', 'Amman')->neighborhoods();
 *   Areas::in('JO', 'Amman')->tree();         // districts with their children
 *
 * @generated-methods-start
 * @method static \Enadstack\CountryData\Support\AreaCollection arab()  Arab League
 * @method static \Enadstack\CountryData\Support\AreaCollection gulf()  Arabian Gulf
 * @method static \Enadstack\CountryData\Support\AreaCollection gcc()  Gulf Cooperation Council
 * @method static \Enadstack\CountryData\Support\AreaCollection levant()  Levant
 * @method static \Enadstack\CountryData\Support\AreaCollection maghreb()  Maghreb
 * @method static \Enadstack\CountryData\Support\AreaCollection middleEast()  Middle East
 * @method static \Enadstack\CountryData\Support\AreaCollection muslimMajority()  Muslim-majority
 * @method static \Enadstack\CountryData\Support\AreaCollection africa()  Africa
 * @method static \Enadstack\CountryData\Support\AreaCollection asia()  Asia
 * @method static \Enadstack\CountryData\Support\AreaCollection europe()  Europe
 * @method static \Enadstack\CountryData\Support\AreaCollection northAmerica()  North America
 * @method static \Enadstack\CountryData\Support\AreaCollection southAmerica()  South America
 * @method static \Enadstack\CountryData\Support\AreaCollection oceania()  Oceania
 * @method static \Enadstack\CountryData\Support\AreaCollection eu()  European Union
 * @method static \Enadstack\CountryData\Support\AreaCollection schengen()  Schengen Area
 * @method static \Enadstack\CountryData\Support\AreaCollection g20()  G20
 * @method static \Enadstack\CountryData\Support\AreaCollection jordan()  JO Jordan
 * @method static \Enadstack\CountryData\Support\AreaCollection saudiArabia()  SA Saudi Arabia
 * @method static \Enadstack\CountryData\Support\AreaCollection unitedArabEmirates()  AE United Arab Emirates
 * @method static \Enadstack\CountryData\Support\AreaCollection kuwait()  KW Kuwait
 * @method static \Enadstack\CountryData\Support\AreaCollection qatar()  QA Qatar
 * @method static \Enadstack\CountryData\Support\AreaCollection oman()  OM Oman
 * @method static \Enadstack\CountryData\Support\AreaCollection bahrain()  BH Bahrain
 * @method static \Enadstack\CountryData\Support\AreaCollection iraq()  IQ Iraq
 * @method static \Enadstack\CountryData\Support\AreaCollection syria()  SY Syria
 * @method static \Enadstack\CountryData\Support\AreaCollection lebanon()  LB Lebanon
 * @method static \Enadstack\CountryData\Support\AreaCollection palestine()  PS Palestine
 * @method static \Enadstack\CountryData\Support\AreaCollection egypt()  EG Egypt
 * @method static \Enadstack\CountryData\Support\AreaCollection libya()  LY Libya
 * @method static \Enadstack\CountryData\Support\AreaCollection tunisia()  TN Tunisia
 * @method static \Enadstack\CountryData\Support\AreaCollection algeria()  DZ Algeria
 * @method static \Enadstack\CountryData\Support\AreaCollection morocco()  MA Morocco
 * @method static \Enadstack\CountryData\Support\AreaCollection sudan()  SD Sudan
 * @method static \Enadstack\CountryData\Support\AreaCollection yemen()  YE Yemen
 * @method static \Enadstack\CountryData\Support\AreaCollection mauritania()  MR Mauritania
 * @method static \Enadstack\CountryData\Support\AreaCollection somalia()  SO Somalia
 * @method static \Enadstack\CountryData\Support\AreaCollection djibouti()  DJ Djibouti
 * @method static \Enadstack\CountryData\Support\AreaCollection comoros()  KM Comoros
 * @method static \Enadstack\CountryData\Support\AreaCollection afghanistan()  AF Afghanistan
 * @method static \Enadstack\CountryData\Support\AreaCollection albania()  AL Albania
 * @method static \Enadstack\CountryData\Support\AreaCollection americanSamoa()  AS American Samoa
 * @method static \Enadstack\CountryData\Support\AreaCollection andorra()  AD Andorra
 * @method static \Enadstack\CountryData\Support\AreaCollection angola()  AO Angola
 * @method static \Enadstack\CountryData\Support\AreaCollection anguilla()  AI Anguilla
 * @method static \Enadstack\CountryData\Support\AreaCollection antarctica()  AQ Antarctica
 * @method static \Enadstack\CountryData\Support\AreaCollection antiguaAndBarbuda()  AG Antigua and Barbuda
 * @method static \Enadstack\CountryData\Support\AreaCollection argentina()  AR Argentina
 * @method static \Enadstack\CountryData\Support\AreaCollection armenia()  AM Armenia
 * @method static \Enadstack\CountryData\Support\AreaCollection aruba()  AW Aruba
 * @method static \Enadstack\CountryData\Support\AreaCollection australia()  AU Australia
 * @method static \Enadstack\CountryData\Support\AreaCollection austria()  AT Austria
 * @method static \Enadstack\CountryData\Support\AreaCollection azerbaijan()  AZ Azerbaijan
 * @method static \Enadstack\CountryData\Support\AreaCollection bahamas()  BS Bahamas
 * @method static \Enadstack\CountryData\Support\AreaCollection bangladesh()  BD Bangladesh
 * @method static \Enadstack\CountryData\Support\AreaCollection barbados()  BB Barbados
 * @method static \Enadstack\CountryData\Support\AreaCollection belarus()  BY Belarus
 * @method static \Enadstack\CountryData\Support\AreaCollection belgium()  BE Belgium
 * @method static \Enadstack\CountryData\Support\AreaCollection belize()  BZ Belize
 * @method static \Enadstack\CountryData\Support\AreaCollection benin()  BJ Benin
 * @method static \Enadstack\CountryData\Support\AreaCollection bermuda()  BM Bermuda
 * @method static \Enadstack\CountryData\Support\AreaCollection bhutan()  BT Bhutan
 * @method static \Enadstack\CountryData\Support\AreaCollection bolivia()  BO Bolivia
 * @method static \Enadstack\CountryData\Support\AreaCollection bosniaAndHerzegovina()  BA Bosnia and Herzegovina
 * @method static \Enadstack\CountryData\Support\AreaCollection botswana()  BW Botswana
 * @method static \Enadstack\CountryData\Support\AreaCollection bouvetIsland()  BV Bouvet Island
 * @method static \Enadstack\CountryData\Support\AreaCollection brazil()  BR Brazil
 * @method static \Enadstack\CountryData\Support\AreaCollection britishIndianOceanTerritory()  IO British Indian Ocean Territory
 * @method static \Enadstack\CountryData\Support\AreaCollection britishVirginIslands()  VG British Virgin Islands
 * @method static \Enadstack\CountryData\Support\AreaCollection brunei()  BN Brunei
 * @method static \Enadstack\CountryData\Support\AreaCollection bulgaria()  BG Bulgaria
 * @method static \Enadstack\CountryData\Support\AreaCollection burkinaFaso()  BF Burkina Faso
 * @method static \Enadstack\CountryData\Support\AreaCollection burundi()  BI Burundi
 * @method static \Enadstack\CountryData\Support\AreaCollection cambodia()  KH Cambodia
 * @method static \Enadstack\CountryData\Support\AreaCollection cameroon()  CM Cameroon
 * @method static \Enadstack\CountryData\Support\AreaCollection canada()  CA Canada
 * @method static \Enadstack\CountryData\Support\AreaCollection capeVerde()  CV Cape Verde
 * @method static \Enadstack\CountryData\Support\AreaCollection caribbeanNetherlands()  BQ Caribbean Netherlands
 * @method static \Enadstack\CountryData\Support\AreaCollection caymanIslands()  KY Cayman Islands
 * @method static \Enadstack\CountryData\Support\AreaCollection centralAfricanRepublic()  CF Central African Republic
 * @method static \Enadstack\CountryData\Support\AreaCollection chad()  TD Chad
 * @method static \Enadstack\CountryData\Support\AreaCollection chile()  CL Chile
 * @method static \Enadstack\CountryData\Support\AreaCollection china()  CN China
 * @method static \Enadstack\CountryData\Support\AreaCollection christmasIsland()  CX Christmas Island
 * @method static \Enadstack\CountryData\Support\AreaCollection cocosKeelingIslands()  CC Cocos (Keeling) Islands
 * @method static \Enadstack\CountryData\Support\AreaCollection colombia()  CO Colombia
 * @method static \Enadstack\CountryData\Support\AreaCollection congo()  CG Congo
 * @method static \Enadstack\CountryData\Support\AreaCollection cookIslands()  CK Cook Islands
 * @method static \Enadstack\CountryData\Support\AreaCollection costaRica()  CR Costa Rica
 * @method static \Enadstack\CountryData\Support\AreaCollection croatia()  HR Croatia
 * @method static \Enadstack\CountryData\Support\AreaCollection cuba()  CU Cuba
 * @method static \Enadstack\CountryData\Support\AreaCollection curacao()  CW Curaçao
 * @method static \Enadstack\CountryData\Support\AreaCollection cyprus()  CY Cyprus
 * @method static \Enadstack\CountryData\Support\AreaCollection czechia()  CZ Czechia
 * @method static \Enadstack\CountryData\Support\AreaCollection drCongo()  CD DR Congo
 * @method static \Enadstack\CountryData\Support\AreaCollection denmark()  DK Denmark
 * @method static \Enadstack\CountryData\Support\AreaCollection dominica()  DM Dominica
 * @method static \Enadstack\CountryData\Support\AreaCollection dominicanRepublic()  DO Dominican Republic
 * @method static \Enadstack\CountryData\Support\AreaCollection ecuador()  EC Ecuador
 * @method static \Enadstack\CountryData\Support\AreaCollection elSalvador()  SV El Salvador
 * @method static \Enadstack\CountryData\Support\AreaCollection equatorialGuinea()  GQ Equatorial Guinea
 * @method static \Enadstack\CountryData\Support\AreaCollection eritrea()  ER Eritrea
 * @method static \Enadstack\CountryData\Support\AreaCollection estonia()  EE Estonia
 * @method static \Enadstack\CountryData\Support\AreaCollection eswatini()  SZ Eswatini
 * @method static \Enadstack\CountryData\Support\AreaCollection ethiopia()  ET Ethiopia
 * @method static \Enadstack\CountryData\Support\AreaCollection falklandIslands()  FK Falkland Islands
 * @method static \Enadstack\CountryData\Support\AreaCollection faroeIslands()  FO Faroe Islands
 * @method static \Enadstack\CountryData\Support\AreaCollection fiji()  FJ Fiji
 * @method static \Enadstack\CountryData\Support\AreaCollection finland()  FI Finland
 * @method static \Enadstack\CountryData\Support\AreaCollection france()  FR France
 * @method static \Enadstack\CountryData\Support\AreaCollection frenchGuiana()  GF French Guiana
 * @method static \Enadstack\CountryData\Support\AreaCollection frenchPolynesia()  PF French Polynesia
 * @method static \Enadstack\CountryData\Support\AreaCollection frenchSouthernAndAntarcticLands()  TF French Southern and Antarctic Lands
 * @method static \Enadstack\CountryData\Support\AreaCollection gabon()  GA Gabon
 * @method static \Enadstack\CountryData\Support\AreaCollection gambia()  GM Gambia
 * @method static \Enadstack\CountryData\Support\AreaCollection georgia()  GE Georgia
 * @method static \Enadstack\CountryData\Support\AreaCollection germany()  DE Germany
 * @method static \Enadstack\CountryData\Support\AreaCollection ghana()  GH Ghana
 * @method static \Enadstack\CountryData\Support\AreaCollection gibraltar()  GI Gibraltar
 * @method static \Enadstack\CountryData\Support\AreaCollection greece()  GR Greece
 * @method static \Enadstack\CountryData\Support\AreaCollection greenland()  GL Greenland
 * @method static \Enadstack\CountryData\Support\AreaCollection grenada()  GD Grenada
 * @method static \Enadstack\CountryData\Support\AreaCollection guadeloupe()  GP Guadeloupe
 * @method static \Enadstack\CountryData\Support\AreaCollection guam()  GU Guam
 * @method static \Enadstack\CountryData\Support\AreaCollection guatemala()  GT Guatemala
 * @method static \Enadstack\CountryData\Support\AreaCollection guernsey()  GG Guernsey
 * @method static \Enadstack\CountryData\Support\AreaCollection guinea()  GN Guinea
 * @method static \Enadstack\CountryData\Support\AreaCollection guineaBissau()  GW Guinea-Bissau
 * @method static \Enadstack\CountryData\Support\AreaCollection guyana()  GY Guyana
 * @method static \Enadstack\CountryData\Support\AreaCollection haiti()  HT Haiti
 * @method static \Enadstack\CountryData\Support\AreaCollection heardIslandAndMcdonaldIslands()  HM Heard Island and McDonald Islands
 * @method static \Enadstack\CountryData\Support\AreaCollection honduras()  HN Honduras
 * @method static \Enadstack\CountryData\Support\AreaCollection hongKong()  HK Hong Kong
 * @method static \Enadstack\CountryData\Support\AreaCollection hungary()  HU Hungary
 * @method static \Enadstack\CountryData\Support\AreaCollection iceland()  IS Iceland
 * @method static \Enadstack\CountryData\Support\AreaCollection india()  IN India
 * @method static \Enadstack\CountryData\Support\AreaCollection indonesia()  ID Indonesia
 * @method static \Enadstack\CountryData\Support\AreaCollection iran()  IR Iran
 * @method static \Enadstack\CountryData\Support\AreaCollection ireland()  IE Ireland
 * @method static \Enadstack\CountryData\Support\AreaCollection isleOfMan()  IM Isle of Man
 * @method static \Enadstack\CountryData\Support\AreaCollection israel()  IL Israel
 * @method static \Enadstack\CountryData\Support\AreaCollection italy()  IT Italy
 * @method static \Enadstack\CountryData\Support\AreaCollection ivoryCoast()  CI Ivory Coast
 * @method static \Enadstack\CountryData\Support\AreaCollection jamaica()  JM Jamaica
 * @method static \Enadstack\CountryData\Support\AreaCollection japan()  JP Japan
 * @method static \Enadstack\CountryData\Support\AreaCollection jersey()  JE Jersey
 * @method static \Enadstack\CountryData\Support\AreaCollection kazakhstan()  KZ Kazakhstan
 * @method static \Enadstack\CountryData\Support\AreaCollection kenya()  KE Kenya
 * @method static \Enadstack\CountryData\Support\AreaCollection kiribati()  KI Kiribati
 * @method static \Enadstack\CountryData\Support\AreaCollection kosovo()  XK Kosovo
 * @method static \Enadstack\CountryData\Support\AreaCollection kyrgyzstan()  KG Kyrgyzstan
 * @method static \Enadstack\CountryData\Support\AreaCollection laos()  LA Laos
 * @method static \Enadstack\CountryData\Support\AreaCollection latvia()  LV Latvia
 * @method static \Enadstack\CountryData\Support\AreaCollection lesotho()  LS Lesotho
 * @method static \Enadstack\CountryData\Support\AreaCollection liberia()  LR Liberia
 * @method static \Enadstack\CountryData\Support\AreaCollection liechtenstein()  LI Liechtenstein
 * @method static \Enadstack\CountryData\Support\AreaCollection lithuania()  LT Lithuania
 * @method static \Enadstack\CountryData\Support\AreaCollection luxembourg()  LU Luxembourg
 * @method static \Enadstack\CountryData\Support\AreaCollection macau()  MO Macau
 * @method static \Enadstack\CountryData\Support\AreaCollection madagascar()  MG Madagascar
 * @method static \Enadstack\CountryData\Support\AreaCollection malawi()  MW Malawi
 * @method static \Enadstack\CountryData\Support\AreaCollection malaysia()  MY Malaysia
 * @method static \Enadstack\CountryData\Support\AreaCollection maldives()  MV Maldives
 * @method static \Enadstack\CountryData\Support\AreaCollection mali()  ML Mali
 * @method static \Enadstack\CountryData\Support\AreaCollection malta()  MT Malta
 * @method static \Enadstack\CountryData\Support\AreaCollection marshallIslands()  MH Marshall Islands
 * @method static \Enadstack\CountryData\Support\AreaCollection martinique()  MQ Martinique
 * @method static \Enadstack\CountryData\Support\AreaCollection mauritius()  MU Mauritius
 * @method static \Enadstack\CountryData\Support\AreaCollection mayotte()  YT Mayotte
 * @method static \Enadstack\CountryData\Support\AreaCollection mexico()  MX Mexico
 * @method static \Enadstack\CountryData\Support\AreaCollection micronesia()  FM Micronesia
 * @method static \Enadstack\CountryData\Support\AreaCollection moldova()  MD Moldova
 * @method static \Enadstack\CountryData\Support\AreaCollection monaco()  MC Monaco
 * @method static \Enadstack\CountryData\Support\AreaCollection mongolia()  MN Mongolia
 * @method static \Enadstack\CountryData\Support\AreaCollection montenegro()  ME Montenegro
 * @method static \Enadstack\CountryData\Support\AreaCollection montserrat()  MS Montserrat
 * @method static \Enadstack\CountryData\Support\AreaCollection mozambique()  MZ Mozambique
 * @method static \Enadstack\CountryData\Support\AreaCollection myanmar()  MM Myanmar
 * @method static \Enadstack\CountryData\Support\AreaCollection namibia()  NA Namibia
 * @method static \Enadstack\CountryData\Support\AreaCollection nauru()  NR Nauru
 * @method static \Enadstack\CountryData\Support\AreaCollection nepal()  NP Nepal
 * @method static \Enadstack\CountryData\Support\AreaCollection netherlands()  NL Netherlands
 * @method static \Enadstack\CountryData\Support\AreaCollection newCaledonia()  NC New Caledonia
 * @method static \Enadstack\CountryData\Support\AreaCollection newZealand()  NZ New Zealand
 * @method static \Enadstack\CountryData\Support\AreaCollection nicaragua()  NI Nicaragua
 * @method static \Enadstack\CountryData\Support\AreaCollection niger()  NE Niger
 * @method static \Enadstack\CountryData\Support\AreaCollection nigeria()  NG Nigeria
 * @method static \Enadstack\CountryData\Support\AreaCollection niue()  NU Niue
 * @method static \Enadstack\CountryData\Support\AreaCollection norfolkIsland()  NF Norfolk Island
 * @method static \Enadstack\CountryData\Support\AreaCollection northKorea()  KP North Korea
 * @method static \Enadstack\CountryData\Support\AreaCollection northMacedonia()  MK North Macedonia
 * @method static \Enadstack\CountryData\Support\AreaCollection northernMarianaIslands()  MP Northern Mariana Islands
 * @method static \Enadstack\CountryData\Support\AreaCollection norway()  NO Norway
 * @method static \Enadstack\CountryData\Support\AreaCollection pakistan()  PK Pakistan
 * @method static \Enadstack\CountryData\Support\AreaCollection palau()  PW Palau
 * @method static \Enadstack\CountryData\Support\AreaCollection panama()  PA Panama
 * @method static \Enadstack\CountryData\Support\AreaCollection papuaNewGuinea()  PG Papua New Guinea
 * @method static \Enadstack\CountryData\Support\AreaCollection paraguay()  PY Paraguay
 * @method static \Enadstack\CountryData\Support\AreaCollection peru()  PE Peru
 * @method static \Enadstack\CountryData\Support\AreaCollection philippines()  PH Philippines
 * @method static \Enadstack\CountryData\Support\AreaCollection pitcairnIslands()  PN Pitcairn Islands
 * @method static \Enadstack\CountryData\Support\AreaCollection poland()  PL Poland
 * @method static \Enadstack\CountryData\Support\AreaCollection portugal()  PT Portugal
 * @method static \Enadstack\CountryData\Support\AreaCollection puertoRico()  PR Puerto Rico
 * @method static \Enadstack\CountryData\Support\AreaCollection romania()  RO Romania
 * @method static \Enadstack\CountryData\Support\AreaCollection russia()  RU Russia
 * @method static \Enadstack\CountryData\Support\AreaCollection rwanda()  RW Rwanda
 * @method static \Enadstack\CountryData\Support\AreaCollection reunion()  RE Réunion
 * @method static \Enadstack\CountryData\Support\AreaCollection saintBarthelemy()  BL Saint Barthélemy
 * @method static \Enadstack\CountryData\Support\AreaCollection saintHelenaAscensionAndTristanDaCunha()  SH Saint Helena, Ascension and Tristan da Cunha
 * @method static \Enadstack\CountryData\Support\AreaCollection saintKittsAndNevis()  KN Saint Kitts and Nevis
 * @method static \Enadstack\CountryData\Support\AreaCollection saintLucia()  LC Saint Lucia
 * @method static \Enadstack\CountryData\Support\AreaCollection saintMartin()  MF Saint Martin
 * @method static \Enadstack\CountryData\Support\AreaCollection saintPierreAndMiquelon()  PM Saint Pierre and Miquelon
 * @method static \Enadstack\CountryData\Support\AreaCollection saintVincentAndTheGrenadines()  VC Saint Vincent and the Grenadines
 * @method static \Enadstack\CountryData\Support\AreaCollection samoa()  WS Samoa
 * @method static \Enadstack\CountryData\Support\AreaCollection sanMarino()  SM San Marino
 * @method static \Enadstack\CountryData\Support\AreaCollection senegal()  SN Senegal
 * @method static \Enadstack\CountryData\Support\AreaCollection serbia()  RS Serbia
 * @method static \Enadstack\CountryData\Support\AreaCollection seychelles()  SC Seychelles
 * @method static \Enadstack\CountryData\Support\AreaCollection sierraLeone()  SL Sierra Leone
 * @method static \Enadstack\CountryData\Support\AreaCollection singapore()  SG Singapore
 * @method static \Enadstack\CountryData\Support\AreaCollection sintMaarten()  SX Sint Maarten
 * @method static \Enadstack\CountryData\Support\AreaCollection slovakia()  SK Slovakia
 * @method static \Enadstack\CountryData\Support\AreaCollection slovenia()  SI Slovenia
 * @method static \Enadstack\CountryData\Support\AreaCollection solomonIslands()  SB Solomon Islands
 * @method static \Enadstack\CountryData\Support\AreaCollection southAfrica()  ZA South Africa
 * @method static \Enadstack\CountryData\Support\AreaCollection southGeorgia()  GS South Georgia
 * @method static \Enadstack\CountryData\Support\AreaCollection southKorea()  KR South Korea
 * @method static \Enadstack\CountryData\Support\AreaCollection southSudan()  SS South Sudan
 * @method static \Enadstack\CountryData\Support\AreaCollection spain()  ES Spain
 * @method static \Enadstack\CountryData\Support\AreaCollection sriLanka()  LK Sri Lanka
 * @method static \Enadstack\CountryData\Support\AreaCollection suriname()  SR Suriname
 * @method static \Enadstack\CountryData\Support\AreaCollection svalbardAndJanMayen()  SJ Svalbard and Jan Mayen
 * @method static \Enadstack\CountryData\Support\AreaCollection sweden()  SE Sweden
 * @method static \Enadstack\CountryData\Support\AreaCollection switzerland()  CH Switzerland
 * @method static \Enadstack\CountryData\Support\AreaCollection saoTomeAndPrincipe()  ST São Tomé and Príncipe
 * @method static \Enadstack\CountryData\Support\AreaCollection taiwan()  TW Taiwan
 * @method static \Enadstack\CountryData\Support\AreaCollection tajikistan()  TJ Tajikistan
 * @method static \Enadstack\CountryData\Support\AreaCollection tanzania()  TZ Tanzania
 * @method static \Enadstack\CountryData\Support\AreaCollection thailand()  TH Thailand
 * @method static \Enadstack\CountryData\Support\AreaCollection timorLeste()  TL Timor-Leste
 * @method static \Enadstack\CountryData\Support\AreaCollection togo()  TG Togo
 * @method static \Enadstack\CountryData\Support\AreaCollection tokelau()  TK Tokelau
 * @method static \Enadstack\CountryData\Support\AreaCollection tonga()  TO Tonga
 * @method static \Enadstack\CountryData\Support\AreaCollection trinidadAndTobago()  TT Trinidad and Tobago
 * @method static \Enadstack\CountryData\Support\AreaCollection turkmenistan()  TM Turkmenistan
 * @method static \Enadstack\CountryData\Support\AreaCollection turksAndCaicosIslands()  TC Turks and Caicos Islands
 * @method static \Enadstack\CountryData\Support\AreaCollection tuvalu()  TV Tuvalu
 * @method static \Enadstack\CountryData\Support\AreaCollection turkiye()  TR Türkiye
 * @method static \Enadstack\CountryData\Support\AreaCollection uganda()  UG Uganda
 * @method static \Enadstack\CountryData\Support\AreaCollection ukraine()  UA Ukraine
 * @method static \Enadstack\CountryData\Support\AreaCollection unitedKingdom()  GB United Kingdom
 * @method static \Enadstack\CountryData\Support\AreaCollection unitedStates()  US United States
 * @method static \Enadstack\CountryData\Support\AreaCollection unitedStatesMinorOutlyingIslands()  UM United States Minor Outlying Islands
 * @method static \Enadstack\CountryData\Support\AreaCollection unitedStatesVirginIslands()  VI United States Virgin Islands
 * @method static \Enadstack\CountryData\Support\AreaCollection uruguay()  UY Uruguay
 * @method static \Enadstack\CountryData\Support\AreaCollection uzbekistan()  UZ Uzbekistan
 * @method static \Enadstack\CountryData\Support\AreaCollection vanuatu()  VU Vanuatu
 * @method static \Enadstack\CountryData\Support\AreaCollection vaticanCity()  VA Vatican City
 * @method static \Enadstack\CountryData\Support\AreaCollection venezuela()  VE Venezuela
 * @method static \Enadstack\CountryData\Support\AreaCollection vietnam()  VN Vietnam
 * @method static \Enadstack\CountryData\Support\AreaCollection wallisAndFutuna()  WF Wallis and Futuna
 * @method static \Enadstack\CountryData\Support\AreaCollection westernSahara()  EH Western Sahara
 * @method static \Enadstack\CountryData\Support\AreaCollection zambia()  ZM Zambia
 * @method static \Enadstack\CountryData\Support\AreaCollection zimbabwe()  ZW Zimbabwe
 * @method static \Enadstack\CountryData\Support\AreaCollection alandIslands()  AX Åland Islands
 * @generated-methods-end
 */
final class Areas extends Shortcut
{
    /** All areas in a country. */
    public static function of(Country|CountryCode|string $country): AreaCollection
    {
        return static::geo()->areasInCountry(static::country($country)->code);
    }

    /**
     * Areas of one city (a City, or a city name within the country), or of the
     * whole country when no city is given.
     *
     * @throws \Enadstack\CountryData\Exceptions\CityNotFoundException
     */
    public static function in(Country|CountryCode|string $country, City|string|null $city = null): AreaCollection
    {
        if ($city === null) {
            return static::of($country);
        }

        $city  = $city instanceof City ? $city : Cities::named($country, $city);
        $areas = static::geo()->areas($city);

        // A cache warmed by v2 holds a plain Collection under the same key.
        return $areas instanceof AreaCollection ? $areas : new AreaCollection($areas->all());
    }

    /** All areas in every country of a region. */
    public static function inRegion(Region|string $region): AreaCollection
    {
        return static::geo()->areasInRegion($region);
    }

    public static function __callStatic(string $name, array $arguments): AreaCollection
    {
        $target = static::resolve($name);

        return $target instanceof Region ? static::inRegion($target) : static::of($target);
    }
}
