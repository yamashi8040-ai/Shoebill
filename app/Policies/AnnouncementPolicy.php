<?php

namespace App\Policies;

use App\Models\User;

/** Announcements reach every user, so only root admins (allowed by the global Gate::before) manage them. */
class AnnouncementPolicy
{
    public function __call(string $name, array $arguments): bool
    {
        return false;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }
}
