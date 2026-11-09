<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

/** Kader Karang Taruna mencatat kegiatannya sendiri; admin mengelola semua. */
class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Activity $activity): bool
    {
        return $user->isAdmin() || (int) $activity->created_by === (int) $user->id;
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $this->update($user, $activity);
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
