<?php

namespace App\Tests\Integration\Commands;

use App\Enums\WingsType;
use App\Models\Node;
use App\Tests\Integration\IntegrationTestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MigrateFromCommandTest extends IntegrationTestCase
{
    public function test_a_pterodactyl_database_keeps_its_nodes_on_pterodactyl_wings(): void
    {
        // The real conversion is covered by PterodactylUpgradeTest on MySQL; here only the detection and the follow-up.
        Schema::create('nests', fn (Blueprint $table) => $table->id());
        $node = Node::factory()->create();

        try {
            $this->artisan('shoebill:migrate-from', ['--force' => true])
                ->expectsOutputToContain('Detected: Pterodactyl')
                ->assertSuccessful();
        } finally {
            Schema::dropIfExists('nests');
        }

        $this->assertSame(WingsType::Pterodactyl, $node->refresh()->daemon_type);
    }

    public function test_a_shoebill_database_is_left_on_its_wings(): void
    {
        $node = Node::factory()->create();

        $this->artisan('shoebill:migrate-from', ['--force' => true])
            ->expectsOutputToContain('Detected: Shoebill')
            ->assertSuccessful();

        $this->assertSame(WingsType::Pelican, $node->refresh()->daemon_type);
    }
}
