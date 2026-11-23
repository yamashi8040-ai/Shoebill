<?php

namespace App\Filament\Components\Forms;

use App\Enums\ShoebillFeature;
use App\Enums\TablerIcon;
use App\Models\Node;
use App\Models\Server;
use App\Services\Features\FeatureService;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs\Tab;

/** A "Features" tab for the node and server edit pages: inherit / on / off per feature, saved as soon as it is clicked. */
class FeatureOverrides
{
    public static function tab(string $scope): Tab
    {
        $fields = collect(ShoebillFeature::cases())
            ->filter(fn (ShoebillFeature $feature) => $feature->isServerFeature())
            ->map(fn (ShoebillFeature $feature) => ToggleButtons::make('feature_' . $feature->value)
                ->label($feature->getLabel())
                ->helperText($feature->getDescription())
                ->inline()
                ->required()
                ->dehydrated(false)
                ->live()
                ->options(['inherit' => trans('admin/feature.inherit'), 'on' => trans('admin/feature.on'), 'off' => trans('admin/feature.off')])
                ->colors(['inherit' => 'gray', 'on' => 'success', 'off' => 'danger'])
                ->formatStateUsing(function (Node|Server|null $record) use ($feature, $scope) {
                    $value = $record ? app(FeatureService::class)->get($feature, $scope, $record->id) : null;

                    return $value === null ? 'inherit' : ($value ? 'on' : 'off');
                })
                ->afterStateUpdated(function (?string $state, Node|Server|null $record) use ($feature, $scope) {
                    if (!$record) {
                        return;
                    }
                    app(FeatureService::class)->set($feature, $scope, match ($state) {
                        'on' => true, 'off' => false, default => null
                    }, $record->id);
                    Notification::make()->success()->title(trans('admin/feature.saved'))->send();
                }))
            ->values()
            ->all();

        return Tab::make('shoebill_features')
            ->label(trans('admin/feature.nav_title'))
            ->icon(TablerIcon::ToggleRight)
            ->hidden(fn (string $operation) => $operation !== 'edit')
            ->schema($fields);
    }
}
