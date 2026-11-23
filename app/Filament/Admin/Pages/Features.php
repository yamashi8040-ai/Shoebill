<?php

namespace App\Filament\Admin\Pages;

use App\Enums\ShoebillFeature;
use App\Enums\TablerIcon;
use App\Facades\Activity;
use App\Services\Features\FeatureService;
use BackedEnum;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Support\Facades\DB;

/** Panel-wide switches for Shoebill features. Nodes and servers can still override server features on their Features tab. */
class Features extends Page implements HasSchemas
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = TablerIcon::ToggleRight;

    protected string $view = 'filament.pages.features';

    /** @var array<string, string> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public static function canAccess(): bool
    {
        return user()?->can('view settings') ?? false;
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('admin/dashboard.advanced');
    }

    public function getTitle(): string
    {
        return trans('admin/feature.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('admin/feature.nav_title');
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    protected function getFormSchema(): array
    {
        $features = app(FeatureService::class);

        return [
            Section::make()
                ->description(trans('admin/feature.global_description'))
                ->disabled(fn () => !user()?->can('update settings'))
                ->schema(collect(ShoebillFeature::cases())->map(fn (ShoebillFeature $feature) => ToggleButtons::make($feature->value)
                    ->label($feature->getLabel())
                    ->helperText(fn () => $this->helperText($feature))
                    ->inline()
                    ->live()
                    ->options(['on' => trans('admin/feature.on'), 'off' => trans('admin/feature.off')])
                    ->colors(['on' => 'success', 'off' => 'danger'])
                    ->icons(['on' => TablerIcon::Check, 'off' => TablerIcon::X])
                    ->formatStateUsing(fn () => ($features->get($feature, FeatureService::GLOBAL) ?? true) ? 'on' : 'off')
                    ->afterStateUpdated(function (?string $state) use ($feature, $features) {
                        abort_unless(user()?->can('update settings'), 403);
                        // "On" is the default, so it is stored as no row at all.
                        $features->set($feature, FeatureService::GLOBAL, $state === 'off' ? false : null);
                        Activity::event($state === 'off' ? 'audit:feature.disable' : 'audit:feature.enable')->property('feature', $feature->getLabel())->log();
                        Notification::make()->success()->title(trans('admin/feature.saved'))->send();
                    }))->all()),
        ];
    }

    private function helperText(ShoebillFeature $feature): string
    {
        $overrides = DB::table('feature_settings')->where('feature', $feature->value)->where('scope', '!=', FeatureService::GLOBAL)
            ->selectRaw('scope, count(*) as total')->groupBy('scope')->pluck('total', 'scope');

        if ($overrides->isEmpty()) {
            return $feature->getDescription();
        }

        return $feature->getDescription() . ' ' . trans('admin/feature.overridden', [
            'nodes' => $overrides[FeatureService::NODE] ?? 0,
            'servers' => $overrides[FeatureService::SERVER] ?? 0,
        ]);
    }
}
