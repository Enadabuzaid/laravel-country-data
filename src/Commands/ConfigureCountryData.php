<?php

namespace Enad\CountryData\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ConfigureCountryData extends Command
{
    protected $signature = 'country-data:configure';
    protected $description = 'Configure the country data source and publish the correct countries.php config';

    public function handle(): void
    {
        $choices = [
            'all' => 'All Countries',
            'arab' => 'Arab Countries Only',
            'gulf' => 'Gulf Countries Only',
            'europe' => 'European Countries Only',
        ];

        $selected = $this->choice(
            'Which country list do you want to use?',
            array_values($choices),
            0
        );

        $sourceKey = array_search($selected, $choices);

        // 1. Save selected source in country-data.php
        File::put(config_path('country-data.php'), <<<PHP
<?php

return [
    'source' => '$sourceKey',
];
PHP);

        // 2. Load from internal source file
        $sourcePath = __DIR__ . '/../../config/source/countries-' . $sourceKey . '.php';
        $targetPath = config_path('countries.php');

        if (!File::exists($sourcePath)) {
            $this->error("❌ Missing internal source file: $sourcePath");
            return;
        }

        // 3. Copy selected list into countries.php
        File::copy($sourcePath, $targetPath);

        $this->info("✔ country-data.php set to source: '$sourceKey'");
        $this->info("✔ countries.php published with $selected");
    }
}
