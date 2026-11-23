<?php

namespace App\Tests\Integration\Services\Features;

use App\Enums\ShoebillFeature;
use App\Services\Features\FeatureService;
use App\Tests\Integration\IntegrationTestCase;

class FeatureServiceTest extends IntegrationTestCase
{
    public function test_server_overrides_node_overrides_global(): void
    {
        $server = $this->createServerModel();
        $features = app(FeatureService::class);
        $f = ShoebillFeature::CrashAlerts;

        $this->assertTrue($features->enabled($f, $server));
        $features->set($f, FeatureService::GLOBAL, false);
        $this->assertFalse($features->enabled($f, $server));
        $features->set($f, FeatureService::NODE, true, $server->node_id);
        $this->assertTrue($features->enabled($f, $server));
        $features->set($f, FeatureService::SERVER, false, $server->id);
        $this->assertFalse($features->enabled($f, $server));
        $features->set($f, FeatureService::SERVER, null, $server->id);
        $this->assertTrue($features->enabled($f, $server));
    }
}
