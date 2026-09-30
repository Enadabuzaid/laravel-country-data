<?php

namespace Enadstack\CountryData\Tests\Unit;

use PHPUnit\Framework\TestCase;

class PackageMetadataTest extends TestCase
{
    private function composer(): array
    {
        return json_decode(file_get_contents(__DIR__ . '/../../composer.json'), true);
    }

    public function test_composer_json_has_no_hardcoded_version(): void
    {
        // Packagist reads versions from git tags; a "version" key fights them.
        $this->assertArrayNotHasKey('version', $this->composer());
    }

    public function test_composer_json_does_not_lower_minimum_stability(): void
    {
        $this->assertArrayNotHasKey('minimum-stability', $this->composer());
    }

    public function test_empty_install_command_file_is_gone(): void
    {
        $this->assertFileDoesNotExist(__DIR__ . '/../../src/Commands/InstallCountryData.php');
    }
}
