<?php

namespace App\Tests\Integration\Services\Eggs;

use App\Enums\EggFormat;
use App\Models\Egg;
use App\Services\Eggs\Sharing\EggExporterService;
use App\Services\Eggs\Sharing\EggImporterService;
use App\Tests\Integration\IntegrationTestCase;

class PterodactylEggExportTest extends IntegrationTestCase
{
    public function test_ptdl_export_round_trips_through_the_importer(): void
    {
        $egg = $this->createServerModel()->egg;
        $json = app(EggExporterService::class)->toPterodactyl($egg->id);
        $parsed = json_decode($json, true);

        $this->assertSame('PTDL_v2', $parsed['meta']['version']);
        $this->assertIsString($parsed['startup']);
        $this->assertIsString($parsed['variables'][0]['rules'] ?? '');

        $imported = app(EggImporterService::class)->fromContent(str_replace($egg->name, 'Round trip', $json), EggFormat::JSON);

        $this->assertInstanceOf(Egg::class, $imported);
        $this->assertSame('Round trip', $imported->name);
        $this->assertSame(array_values($egg->startup_commands)[0], array_values($imported->startup_commands)[0]);
        $this->assertSame($egg->variables->count(), $imported->variables->count());
    }
}
