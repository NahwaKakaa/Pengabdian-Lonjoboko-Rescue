<?php

namespace App\Policies;

use App\Models\User;

/**
 * Konten publik (informasi, edukasi, kontak, relawan, akun pengguna)
 * hanya dikelola oleh admin desa.
 */
abstract class AdminOnlyPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : false;
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user): bool
    {
        return false;
    }

    public function delete(User $user): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
