<?php

namespace Enad\CountryData\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishFrontendComponent extends Command
{
    protected $signature = 'country-data:publish-component';
    protected $description = 'Publish frontend component for country select (Blade, Vue, React)';

    public function handle(): void
    {
        $type = $this->choice('Which frontend type to publish?', ['blade', 'vue', 'react'], 0);

        $base = __DIR__ . '/../resources';

        $sourcePath = match ($type) {
            'blade' => realpath($base . '/views/components/country-select.blade.php'),
            'vue'   => realpath($base . '/js/vue/CountrySelect.vue'),
            'react' => realpath($base . '/js/react/CountrySelect.jsx'),
        };

        $targetPath = match ($type) {
            'blade' => base_path('resources/views/components/country-select.blade.php'),
            'vue'   => base_path('resources/js/components/custom/CountrySelect.vue'),
            'react' => base_path('resources/js/components/custom/CountrySelect.jsx'),
        };

        $this->info("Looking for: $sourcePath");

        if (!File::exists($sourcePath)) {
            $this->error("❌ Component file not found for $type at: $sourcePath");
            return;
        }

        File::ensureDirectoryExists(dirname($targetPath));
        File::copy($sourcePath, $targetPath);

        $this->info("✅ $type component published to: $targetPath");
    }
}
