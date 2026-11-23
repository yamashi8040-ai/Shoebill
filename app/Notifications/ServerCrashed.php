<?php

namespace App\Notifications;

use App\Filament\Server\Pages\Console;
use App\Models\Server;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServerCrashed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Server $server) {}

    /** @return string[] */
    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $locale = $notifiable->language ?? 'en';

        return (new MailMessage())
            ->error()
            ->subject(trans('mail.server_crashed.subject', ['name' => $this->server->name], $locale))
            ->greeting(trans('mail.greeting', ['name' => $notifiable->username], $locale))
            ->line(trans('mail.server_crashed.body', locale: $locale))
            ->line(trans('mail.server_installed.server_name', ['name' => $this->server->name], $locale))
            ->action(trans('mail.server_crashed.action', locale: $locale), Console::getUrl(panel: 'server', tenant: $this->server));
    }
}
