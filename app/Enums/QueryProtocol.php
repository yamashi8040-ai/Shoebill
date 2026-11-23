<?php

namespace App\Enums;

use App\Models\Egg;
use Filament\Support\Contracts\HasLabel;

enum QueryProtocol: string implements HasLabel
{
    case None = 'none';
    case MinecraftJava = 'minecraft';
    case MinecraftBedrock = 'bedrock';
    case Source = 'source';

    public function getLabel(): string
    {
        return trans('server/status.protocols.' . $this->value);
    }

    /** The egg's explicit choice, or a guess from its tags, features and name. */
    public static function forEgg(Egg $egg): self
    {
        if ($egg->query_protocol) {
            return self::tryFrom($egg->query_protocol) ?? self::None;
        }

        $haystack = strtolower(implode(' ', [$egg->name, ...($egg->tags ?? []), ...($egg->features ?? [])]));

        return match (true) {
            str_contains($haystack, 'bedrock') || str_contains($haystack, 'pocketmine') || str_contains($haystack, 'nukkit') => self::MinecraftBedrock,
            str_contains($haystack, 'minecraft') || str_contains($haystack, 'eula') || str_contains($haystack, 'java_version') => self::MinecraftJava,
            str_contains($haystack, 'steam') || str_contains($haystack, 'source') || str_contains($haystack, 'gsl_token') => self::Source,
            default => self::None,
        };
    }
}
