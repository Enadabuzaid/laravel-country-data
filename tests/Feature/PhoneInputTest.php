<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Database\Seeders\GeographySeeder;
use Enadstack\CountryData\Facades\Geography;
use Enadstack\CountryData\Rules\ValidPhoneNumber;
use Enadstack\CountryData\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class PhoneInputTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(GeographySeeder::class);
    }

    public function test_phone_countries_for_select_returns_dial_codes_and_flags(): void
    {
        $options = Geography::phoneCountriesForSelect();
        $jordan = $options->firstWhere('value', 'JO');

        $this->assertNotEmpty($options);
        $this->assertSame('Jordan', $jordan['label']);
        $this->assertSame('+962', $jordan['dial']);
        $this->assertSame('🇯🇴', $jordan['flag']);
        $this->assertSame('https://flagcdn.com/jo.svg', $jordan['flag_svg']);
        $this->assertTrue($options->every(fn (array $option) => str_starts_with($option['dial'], '+')));
    }

    public function test_phone_countries_for_select_supports_arabic_labels(): void
    {
        $this->assertSame('الأردن', Geography::phoneCountriesForSelect('ar')->firstWhere('value', 'JO')['label']);
    }

    public function test_normalize_strips_formatting_and_trunk_prefix(): void
    {
        $this->assertSame('772432330', ValidPhoneNumber::normalize('0772 432-330'));
        $this->assertSame('772432330', ValidPhoneNumber::normalize('(077) 243 2330'));
        $this->assertNull(ValidPhoneNumber::normalize('  '));
        $this->assertNull(ValidPhoneNumber::normalize(null));
    }

    public function test_valid_phone_number_passes_for_a_national_number(): void
    {
        $validator = Validator::make(
            ['phone_country_code' => 'JO', 'phone' => '772432330'],
            ['phone' => [new ValidPhoneNumber]],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_valid_phone_number_requires_a_country_code(): void
    {
        $validator = Validator::make(
            ['phone' => '772432330'],
            ['phone' => [new ValidPhoneNumber]],
        );

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('country code', $validator->errors()->first('phone'));
    }

    public function test_valid_phone_number_rejects_non_digits_and_bad_lengths(): void
    {
        foreach (['77abc2330', '1234', '1234567890123456'] as $phone) {
            $validator = Validator::make(
                ['phone_country_code' => 'JO', 'phone' => $phone],
                ['phone' => [new ValidPhoneNumber]],
            );

            $this->assertTrue($validator->fails(), "Phone '{$phone}' should fail");
        }
    }

    public function test_valid_phone_number_reads_a_custom_country_field(): void
    {
        $validator = Validator::make(
            ['mobile_country' => 'SA', 'mobile' => '501234567'],
            ['mobile' => [new ValidPhoneNumber('mobile_country')]],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_publish_command_copies_the_react_phone_input(): void
    {
        $target = resource_path('js/components/custom/PhoneInput.tsx');
        File::delete($target);

        $this->artisan('country-data:publish-component', ['--component' => 'phone-input', '--type' => 'react'])
            ->assertSuccessful();

        $this->assertFileExists($target);
        $this->assertStringContainsString('export function PhoneInput', File::get($target));

        File::delete($target);
    }

    public function test_publish_command_rejects_unknown_components(): void
    {
        $this->artisan('country-data:publish-component', ['--component' => 'nope', '--type' => 'react'])
            ->assertFailed();
    }
}
