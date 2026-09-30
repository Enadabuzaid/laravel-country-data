<?php

namespace Enadstack\CountryData\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishFrontendComponent extends Command
{
    protected $signature = 'country-data:publish-component
                            {--type= : Frontend type (blade, vue, react)}
                            {--component= : Component to publish (country-select, phone-input)}
                            {--force : Overwrite an existing file}';

    protected $description = 'Publish a frontend component (country select or phone input) for Blade, Vue, or React';

    /**
     * Source file (relative to src/resources) and target path (relative to resources/) per component and type.
     *
     * @var array<string, array<string, array{0: string, 1: string}>>
     */
    private const COMPONENTS = [
        'country-select' => [
            'blade' => ['views/components/country-select.blade.php', 'views/components/country-select.blade.php'],
            'vue'   => ['js/vue/CountrySelect.vue', 'js/components/custom/CountrySelect.vue'],
            'react' => ['js/react/CountrySelect.jsx', 'js/components/custom/CountrySelect.jsx'],
        ],
        'phone-input' => [
            'blade' => ['views/components/phone-code-select.blade.php', 'views/components/phone-code-select.blade.php'],
            'vue'   => ['js/vue/PhoneCodeSelect.vue', 'js/components/custom/PhoneCodeSelect.vue'],
            'react' => ['js/react/PhoneInput.tsx', 'js/components/custom/PhoneInput.tsx'],
        ],
    ];

    public function handle(): int
    {
        $component = $this->option('component')
            ?: $this->choice('Which component to publish?', array_keys(self::COMPONENTS), 0);
        $type = $this->option('type')
            ?: $this->choice('Which frontend type to publish?', ['blade', 'vue', 'react'], 0);

        if (! isset(self::COMPONENTS[$component][$type])) {
            $this->error("Unknown component/type combination: {$component} ({$type}).");

            return self::FAILURE;
        }

        [$source, $target] = self::COMPONENTS[$component][$type];
        $sourcePath = __DIR__ . '/../resources/' . $source;
        $targetPath = resource_path($target);

        if (! File::exists($sourcePath)) {
            $this->error("Component file not found for {$type} at: {$sourcePath}");

            return self::FAILURE;
        }

        if (File::exists($targetPath) && ! $this->option('force')
            && ! $this->confirm("{$targetPath} already exists. Overwrite?", false)) {
            $this->warn('Skipped.');

            return self::SUCCESS;
        }

        File::ensureDirectoryExists(dirname($targetPath));
        File::copy($sourcePath, $targetPath);

        $this->info("{$component} ({$type}) published to: {$targetPath}");

        return self::SUCCESS;
    }
}
