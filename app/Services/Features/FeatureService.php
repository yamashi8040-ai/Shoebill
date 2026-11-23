<?php

namespace App\Services\Features;

use App\Enums\ShoebillFeature;
use App\Models\Node;
use App\Models\Server;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Resolves whether a Shoebill feature is on: the server's own switch, else its node's, else the global one, else on. */
class FeatureService
{
    public const GLOBAL = 'global';

    public const NODE = 'node';

    public const SERVER = 'server';

    /** @var Collection<string, bool>|null */
    private ?Collection $rows = null;

    public function enabled(ShoebillFeature $feature, ?Server $server = null): bool
    {
        if ($server && $feature->isServerFeature()) {
            $override = $this->get($feature, self::SERVER, $server->id) ?? $this->get($feature, self::NODE, $server->node_id);
            if ($override !== null) {
                return $override;
            }
        }

        return $this->get($feature, self::GLOBAL) ?? true;
    }

    public function enabledOnNode(ShoebillFeature $feature, Node $node): bool
    {
        return $this->get($feature, self::NODE, $node->id) ?? $this->get($feature, self::GLOBAL) ?? true;
    }

    /** The switch stored at exactly this level; null means it inherits. */
    public function get(ShoebillFeature $feature, string $scope, int $id = 0): ?bool
    {
        $this->rows ??= DB::table('feature_settings')->get()
            ->mapWithKeys(fn ($row) => ["$row->feature|$row->scope|$row->scope_id" => (bool) $row->enabled]);

        return $this->rows->get("$feature->value|$scope|$id");
    }

    /** Stores a switch; null clears it so the level inherits again. */
    public function set(ShoebillFeature $feature, string $scope, ?bool $enabled, int $id = 0): void
    {
        $key = ['feature' => $feature->value, 'scope' => $scope, 'scope_id' => $id];

        $enabled === null
            ? DB::table('feature_settings')->where($key)->delete()
            : DB::table('feature_settings')->updateOrInsert($key, ['enabled' => $enabled]);

        $this->rows = null;
    }
}
