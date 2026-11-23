<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** Which daemon runs on a node. Pterodactyl Wings lacks a few Pelican-only endpoints, which the panel then works around. */
enum WingsType: string implements HasLabel
{
    case Pelican = 'pelican';
    case Pterodactyl = 'pterodactyl';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pelican => 'Pelican Wings',
            self::Pterodactyl => 'Pterodactyl Wings',
        };
    }

    public function configPath(): string
    {
        return match ($this) {
            self::Pelican => '/etc/pelican/config.yml',
            self::Pterodactyl => '/etc/pterodactyl/config.yml',
        };
    }

    public function defaultDataDirectory(): string
    {
        return match ($this) {
            self::Pelican => '/var/lib/pelican/volumes',
            self::Pterodactyl => '/var/lib/pterodactyl/volumes',
        };
    }
}
