<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Aturan untuk data peta (lokasi rawan, jalur, titik kumpul, fasilitas).
 *
 * - Admin: semua akses.
 * - Kader: boleh melihat dan menambah data; boleh mengubah semua data
 *   (perubahan kembali menunggu verifikasi); hanya boleh menghapus
 *   data miliknya yang belum terverifikasi.
 */
abstract class MapDataPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Model $model): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Model $model): bool
    {
        return true;
    }

    public function delete(User $user, Model $model): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return (int) $model->created_by === (int) $user->id && ! $model->isVerified();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function verify(User $user, Model $model): bool
    {
        return $user->isAdmin();
    }
}
