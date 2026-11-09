<?php

namespace App\Policies;

use App\Models\User;

/**
 * Laporan warga: dibuat dari halaman publik, bukan dari panel.
 * Kader Karang Taruna dan admin sama-sama bisa membuka dan menindaklanjuti;
 * hanya admin yang boleh menghapus.
 */
class IncidentReportPolicy
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
        return false;
    }

    public function update(User $user): bool
    {
        return true;
    }

    public function delete(User $user): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
