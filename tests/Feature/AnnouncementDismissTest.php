<?php

use App\Models\Announcement;
use App\Models\User;

it('keeps a dismissed announcement hidden until it is edited', function () {
    $user = User::factory()->create();
    $announcement = Announcement::create(['title' => 'Maintenance', 'type' => 'warning']);
    $visible = fn () => Announcement::query()->active()->notDismissedBy($user)->pluck('id')->all();

    expect($visible())->toBe([$announcement->id]);

    $this->actingAs($user)->post(route('announcements.dismiss', $announcement))->assertNoContent();
    expect($visible())->toBe([]);

    $this->travel(1)->minutes();
    $announcement->update(['title' => 'Maintenance moved']);
    expect($visible())->toBe([$announcement->id]);
});

it('only shows announcements inside their time window', function () {
    Announcement::create(['title' => 'Later', 'type' => 'info', 'starts_at' => now()->addDay()]);
    Announcement::create(['title' => 'Over', 'type' => 'info', 'ends_at' => now()->subMinute()]);
    $now = Announcement::create(['title' => 'Now', 'type' => 'info']);

    expect(Announcement::query()->active()->pluck('id')->all())->toBe([$now->id]);
});
