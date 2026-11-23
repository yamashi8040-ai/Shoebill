<?php

namespace App\Listeners\Server;

use App\Events\Server\Crashed;
use App\Facades\Activity;
use App\Filament\Server\Pages\Console;
use App\Notifications\ServerCrashed;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ServerCrashedListener
{
    public function handle(Crashed $event): void
    {
        $event->server->loadMissing('user');
        // Recorded so the admin dashboard can list recent crashes.
        Activity::event('server:crashed')->subject($event->server)->log();
        $locale = $event->server->user->language ?? 'en';

        Notification::make()
            ->danger()
            ->title(trans('notifications.server_crashed', locale: $locale))
            ->body(trans('server/setting.server_info.server_name', ['name' => $event->server->name], $locale))
            ->actions([
                Action::make('exclude_view')
                    ->button()
                    ->label(trans('notifications.open_server', locale: $locale))
                    ->markAsRead()
                    ->url(fn () => Console::getUrl(panel: 'server', tenant: $event->server)),
            ])
            ->sendToDatabase($event->server->user);

        if (config('panel.email.send_crash_notification', true)) {
            $event->server->user->notify(new ServerCrashed($event->server));
        }
    }
}
