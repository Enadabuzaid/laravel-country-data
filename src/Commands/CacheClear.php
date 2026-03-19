<?php

namespace Enadstack\CountryData\Commands;

use Illuminate\Console\Command;
use Enadstack\CountryData\Services\GeographyService;

class CacheClear extends Command
{
    protected $signature   = 'country-data:cache-clear';
    protected $description = 'Flush all cached geography data (countries, cities, areas)';

    public function handle(GeographyService $geography): int
    {
        $geography->flush();

        $this->info('Geography cache cleared.');

        return self::SUCCESS;
    }
}
