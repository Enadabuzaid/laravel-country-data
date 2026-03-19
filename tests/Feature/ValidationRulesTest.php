<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Tests\TestCase;
use Enadstack\CountryData\Database\Seeders\GeographySeeder;
use Enadstack\CountryData\Rules\ValidCountryCode;
use Enadstack\CountryData\Rules\ValidCityForCountry;
use Enadstack\CountryData\Rules\ValidAreaForCity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ValidationRulesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeographySeeder::class);
    }

    // ── ValidCountryCode ──────────────────────────────────────────────────────

    public function test_valid_country_code_passes(): void
    {
        $validator = Validator::make(
            ['country' => 'JO'],
            ['country' => [new ValidCountryCode]]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_valid_country_code_is_case_insensitive(): void
    {
        foreach (['jo', 'JO', 'Jo'] as $code) {
            $v = Validator::make(['country' => $code], ['country' => [new ValidCountryCode]]);
            $this->assertTrue($v->passes(), "Code '{$code}' should pass");
        }
    }

    public function test_invalid_country_code_fails(): void
    {
        $validator = Validator::make(
            ['country' => 'XX'],
            ['country' => [new ValidCountryCode]]
        );

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('valid country code', $validator->errors()->first('country'));
    }

    public function test_country_code_with_filter_passes_when_matches(): void
    {
        $validator = Validator::make(
            ['country' => 'JO'],
            ['country' => [new ValidCountryCode(filter: 'arab')]]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_country_code_with_filter_fails_when_not_in_group(): void
    {
        // France is not an Arab country in our dataset
        $validator = Validator::make(
            ['country' => 'FR'],
            ['country' => [new ValidCountryCode(filter: 'arab')]]
        );

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('arab', $validator->errors()->first('country'));
    }

    public function test_country_code_with_gulf_filter(): void
    {
        $pass = Validator::make(['c' => 'SA'], ['c' => [new ValidCountryCode('gulf')]]);
        $this->assertTrue($pass->passes());

        $fail = Validator::make(['c' => 'EG'], ['c' => [new ValidCountryCode('gulf')]]);
        $this->assertTrue($fail->fails());
    }

    public function test_inactive_country_fails(): void
    {
        DB::table('countries')->where('code', 'JO')->update(['is_active' => false]);

        $validator = Validator::make(
            ['country' => 'JO'],
            ['country' => [new ValidCountryCode]]
        );

        $this->assertTrue($validator->fails());
    }

    // ── ValidCityForCountry ───────────────────────────────────────────────────

    public function test_valid_city_for_country_passes(): void
    {
        $ammanId = DB::table('cities')
            ->where('country_code', 'JO')
            ->where('name_en', 'Amman')
            ->value('id');

        $validator = Validator::make(
            ['city_id' => $ammanId],
            ['city_id' => [new ValidCityForCountry('JO')]]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_city_from_wrong_country_fails(): void
    {
        // Get a Saudi city
        $riyadhId = DB::table('cities')
            ->where('country_code', 'SA')
            ->where('name_en', 'Riyadh')
            ->value('id');

        // Validate it as if it belongs to Jordan
        $validator = Validator::make(
            ['city_id' => $riyadhId],
            ['city_id' => [new ValidCityForCountry('JO')]]
        );

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('valid city', $validator->errors()->first('city_id'));
    }

    public function test_nonexistent_city_id_fails(): void
    {
        $validator = Validator::make(
            ['city_id' => 999999],
            ['city_id' => [new ValidCityForCountry('JO')]]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_valid_city_for_country_is_case_insensitive(): void
    {
        $ammanId = DB::table('cities')
            ->where('country_code', 'JO')
            ->where('name_en', 'Amman')
            ->value('id');

        foreach (['jo', 'JO', 'Jo'] as $code) {
            $v = Validator::make(['city_id' => $ammanId], ['city_id' => [new ValidCityForCountry($code)]]);
            $this->assertTrue($v->passes(), "Country code '{$code}' should pass");
        }
    }

    public function test_inactive_city_fails(): void
    {
        $ammanId = DB::table('cities')
            ->where('country_code', 'JO')
            ->where('name_en', 'Amman')
            ->value('id');

        DB::table('cities')->where('id', $ammanId)->update(['is_active' => false]);

        $validator = Validator::make(
            ['city_id' => $ammanId],
            ['city_id' => [new ValidCityForCountry('JO')]]
        );

        $this->assertTrue($validator->fails());
    }

    // ── ValidAreaForCity ──────────────────────────────────────────────────────

    public function test_valid_area_for_city_passes(): void
    {
        $ammanId  = DB::table('cities')->where('name_en', 'Amman')->value('id');
        $abdounId = DB::table('areas')->where('city_id', $ammanId)->where('name_en', 'Abdoun')->value('id');

        $validator = Validator::make(
            ['area_id' => $abdounId],
            ['area_id' => [new ValidAreaForCity($ammanId)]]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_area_from_wrong_city_fails(): void
    {
        $ammanId  = DB::table('cities')->where('name_en', 'Amman')->value('id');
        $irbidId  = DB::table('cities')->where('name_en', 'Irbid')->value('id');

        // Get an Irbid area
        $irbidArea = DB::table('areas')->where('city_id', $irbidId)->first();

        // Validate it as if it belongs to Amman
        $validator = Validator::make(
            ['area_id' => $irbidArea->id],
            ['area_id' => [new ValidAreaForCity($ammanId)]]
        );

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('valid area', $validator->errors()->first('area_id'));
    }

    public function test_area_with_type_filter_passes_when_type_matches(): void
    {
        $ammanId    = DB::table('cities')->where('name_en', 'Amman')->value('id');
        $districtId = DB::table('areas')
            ->where('city_id', $ammanId)
            ->where('type', 'district')
            ->value('id');

        $validator = Validator::make(
            ['area_id' => $districtId],
            ['area_id' => [new ValidAreaForCity($ammanId, type: 'district')]]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_area_with_type_filter_fails_when_type_mismatch(): void
    {
        $ammanId    = DB::table('cities')->where('name_en', 'Amman')->value('id');
        $neighborId = DB::table('areas')
            ->where('city_id', $ammanId)
            ->where('type', 'neighborhood')
            ->value('id');

        // Passes neighborhood but rule requires 'district'
        $validator = Validator::make(
            ['area_id' => $neighborId],
            ['area_id' => [new ValidAreaForCity($ammanId, type: 'district')]]
        );

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('district', $validator->errors()->first('area_id'));
    }

    public function test_rule_accepts_city_model_directly(): void
    {
        $amman    = \Enadstack\CountryData\Models\City::where('name_en', 'Amman')->first();
        $abdounId = DB::table('areas')->where('city_id', $amman->id)->where('name_en', 'Abdoun')->value('id');

        $validator = Validator::make(
            ['area_id' => $abdounId],
            ['area_id' => [new ValidAreaForCity($amman)]] // City model, not int
        );

        $this->assertTrue($validator->passes());
    }

    public function test_inactive_area_fails(): void
    {
        $ammanId  = DB::table('cities')->where('name_en', 'Amman')->value('id');
        $abdounId = DB::table('areas')->where('city_id', $ammanId)->where('name_en', 'Abdoun')->value('id');

        DB::table('areas')->where('id', $abdounId)->update(['is_active' => false]);

        $validator = Validator::make(
            ['area_id' => $abdounId],
            ['area_id' => [new ValidAreaForCity($ammanId)]]
        );

        $this->assertTrue($validator->fails());
    }
}
