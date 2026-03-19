<?php

namespace Enadstack\CountryData\Tests\Feature;

use Enadstack\CountryData\Tests\TestCase;
use Enadstack\CountryData\Database\Seeders\GeographySeeder;
use Enadstack\CountryData\Livewire\GeographySelect;
use Livewire\LivewireManager;
use Livewire\Livewire;
use Illuminate\Support\Facades\DB;

class LivewireTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            \Livewire\LivewireServiceProvider::class,
        ]);
    }

    protected function setUp(): void
    {
        if (! class_exists(LivewireManager::class)) {
            $this->markTestSkipped('livewire/livewire is not installed.');
        }

        parent::setUp();
        $this->seed(GeographySeeder::class);
    }

    // ── Initial render ────────────────────────────────────────────────────────

    public function test_component_renders_country_dropdown(): void
    {
        Livewire::test(GeographySelect::class)
            ->assertSee('Select Country')
            ->assertSee('Jordan')
            ->assertSee('Saudi Arabia');
    }

    public function test_component_renders_with_filter(): void
    {
        Livewire::test(GeographySelect::class, ['filter' => 'gulf'])
            ->assertSee('Saudi Arabia')
            ->assertSee('Kuwait');
    }

    public function test_city_dropdown_not_shown_before_country_selected(): void
    {
        Livewire::test(GeographySelect::class)
            ->assertDontSee('Select City');
    }

    public function test_area_dropdown_not_shown_before_city_selected(): void
    {
        Livewire::test(GeographySelect::class)
            ->assertDontSee('Select Area');
    }

    // ── Country selection ─────────────────────────────────────────────────────

    public function test_selecting_country_shows_cities(): void
    {
        Livewire::test(GeographySelect::class)
            ->set('selectedCountry', 'JO')
            ->assertSee('Select City')
            ->assertSee('Amman')
            ->assertSee('Irbid')
            ->assertSee('Zarqa')
            ->assertSee('Aqaba');
    }

    public function test_selecting_country_dispatches_event(): void
    {
        Livewire::test(GeographySelect::class)
            ->set('selectedCountry', 'JO')
            ->assertDispatched('country-selected', code: 'JO');
    }

    public function test_changing_country_resets_city_and_area(): void
    {
        $ammanId = DB::table('cities')->where('name_en', 'Amman')->value('id');

        Livewire::test(GeographySelect::class)
            ->set('selectedCountry', 'JO')
            ->set('selectedCity', $ammanId)
            ->set('selectedCountry', 'SA')  // change country
            ->assertSet('selectedCity', null)
            ->assertSet('selectedArea', null);
    }

    // ── City selection ────────────────────────────────────────────────────────

    public function test_selecting_city_shows_areas(): void
    {
        $ammanId = DB::table('cities')->where('name_en', 'Amman')->value('id');

        Livewire::test(GeographySelect::class)
            ->set('selectedCountry', 'JO')
            ->set('selectedCity', $ammanId)
            ->assertSee('Abdoun')
            ->assertSee('Sweifieh')
            ->assertSee('Khalda');
    }

    public function test_selecting_city_dispatches_event(): void
    {
        $ammanId = DB::table('cities')->where('name_en', 'Amman')->value('id');

        Livewire::test(GeographySelect::class)
            ->set('selectedCountry', 'JO')
            ->set('selectedCity', $ammanId)
            ->assertDispatched('city-selected', cityId: $ammanId);
    }

    public function test_changing_city_resets_area(): void
    {
        $ammanId  = DB::table('cities')->where('name_en', 'Amman')->value('id');
        $irbidId  = DB::table('cities')->where('name_en', 'Irbid')->value('id');
        $abdounId = DB::table('areas')->where('city_id', $ammanId)->value('id');

        Livewire::test(GeographySelect::class)
            ->set('selectedCountry', 'JO')
            ->set('selectedCity', $ammanId)
            ->set('selectedArea', $abdounId)
            ->set('selectedCity', $irbidId)  // change city
            ->assertSet('selectedArea', null);
    }

    // ── Area selection ────────────────────────────────────────────────────────

    public function test_selecting_area_dispatches_event(): void
    {
        $ammanId  = DB::table('cities')->where('name_en', 'Amman')->value('id');
        $abdounId = DB::table('areas')->where('city_id', $ammanId)->where('name_en', 'Abdoun')->value('id');

        Livewire::test(GeographySelect::class)
            ->set('selectedCountry', 'JO')
            ->set('selectedCity', $ammanId)
            ->set('selectedArea', $abdounId)
            ->assertDispatched('area-selected', areaId: $abdounId);
    }

    // ── showAreas = false ─────────────────────────────────────────────────────

    public function test_areas_hidden_when_show_areas_false(): void
    {
        $ammanId = DB::table('cities')->where('name_en', 'Amman')->value('id');

        Livewire::test(GeographySelect::class, ['showAreas' => false])
            ->set('selectedCountry', 'JO')
            ->set('selectedCity', $ammanId)
            ->assertDontSee('Select Area');
    }

    // ── Locale ────────────────────────────────────────────────────────────────

    public function test_arabic_locale_shows_arabic_country_names(): void
    {
        Livewire::test(GeographySelect::class, ['locale' => 'ar'])
            ->assertSee('الأردن')
            ->assertSee('المملكة العربية السعودية');
    }

    public function test_arabic_locale_shows_arabic_city_names(): void
    {
        Livewire::test(GeographySelect::class, ['locale' => 'ar'])
            ->set('selectedCountry', 'JO')
            ->assertSee('عمّان')
            ->assertSee('إربد');
    }

    // ── Props / mount ─────────────────────────────────────────────────────────

    public function test_component_can_be_pre_loaded_with_country(): void
    {
        Livewire::test(GeographySelect::class, ['selectedCountry' => 'JO'])
            ->assertSet('selectedCountry', 'JO')
            ->assertSee('Amman');
    }

    public function test_component_can_be_pre_loaded_with_city(): void
    {
        $ammanId = DB::table('cities')->where('name_en', 'Amman')->value('id');

        Livewire::test(GeographySelect::class, [
            'selectedCountry' => 'JO',
            'selectedCity'    => $ammanId,
        ])
            ->assertSet('selectedCity', $ammanId)
            ->assertSee('Abdoun');
    }

    public function test_custom_field_names_rendered_in_html(): void
    {
        Livewire::test(GeographySelect::class, [
            'countryField' => 'address[country]',
            'cityField'    => 'address[city]',
            'areaField'    => 'address[area]',
        ])
            ->assertSeeHtml('address[country]');
    }
}
