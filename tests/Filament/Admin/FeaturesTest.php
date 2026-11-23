<?php

use App\Enums\ShoebillFeature;
use App\Filament\Admin\Pages\Features;
use App\Models\Role;
use App\Services\Features\FeatureService;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    [$this->admin] = generateTestAccount();
    $this->admin->syncRoles(Role::getRootAdmin());
    $this->actingAs($this->admin);
});
afterEach(fn () => Filament::setCurrentPanel(null));

it('is forbidden for a user without permission', function () {
    [$user] = generateTestAccount();

    $this->actingAs($user);
    livewire(Features::class)->assertForbidden();
});

it('turns a feature off panel-wide and back on without leaving a row', function () {
    $feature = ShoebillFeature::CrashAlerts;

    livewire(Features::class)
        ->assertFormSet([$feature->value => 'on'])
        ->set('data.' . $feature->value, 'off');

    expect(app(FeatureService::class)->get($feature, FeatureService::GLOBAL))->toBeFalse();

    livewire(Features::class)
        ->assertFormSet([$feature->value => 'off'])
        ->set('data.' . $feature->value, 'on');

    expect(DB::table('feature_settings')->where('feature', $feature->value)->exists())->toBeFalse();
});
