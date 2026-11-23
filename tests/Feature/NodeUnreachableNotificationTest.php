<?php

use Filament\Notifications\Notification;
use Illuminate\Http\Client\ConnectionException;
use Livewire\Component;
use Livewire\Livewire;

it('turns an unreachable node into a notification instead of an error page', function () {
    $component = new class extends Component
    {
        public function ping(): void
        {
            throw new ConnectionException('cURL error 6: Could not resolve host');
        }

        public function render(): string
        {
            return '<div></div>';
        }
    };

    Livewire::test($component)->call('ping')->assertOk();

    Notification::assertNotified(trans('notifications.node_unreachable'));
});
