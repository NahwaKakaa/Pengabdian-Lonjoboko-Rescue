<?php

namespace App\Models\Concerns;

use App\Enums\VerificationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Alur verifikasi data peta.
 *
 * - Data yang disimpan oleh kader selalu berstatus "menunggu",
 *   termasuk saat kader mengubah data yang sudah terverifikasi.
 * - Hanya admin yang bisa mengubah status menjadi "terverifikasi"
 *   atau "ditolak".
 * - Hanya data terverifikasi yang tampil di halaman publik.
 */
trait HasVerification
{
    public static function bootHasVerification(): void
    {
        static::creating(function (self $model) {
            if (! $model->created_by && auth()->check()) {
                $model->created_by = auth()->id();
            }

            if (! $model->status) {
                $model->status = VerificationStatus::Menunggu;
            }
        });

        static::saving(function (self $model) {
            /** @var User|null $user */
            $user = auth()->user();

            if ($user && ! $user->isAdmin()) {
                $model->status = VerificationStatus::Menunggu;
            }

            if (! $model->isDirty('status')) {
                return;
            }

            if ($model->status === VerificationStatus::Terverifikasi) {
                $model->verified_by = $user?->id;
                $model->verified_at = now();
                $model->review_note = null;
            } else {
                $model->verified_by = null;
                $model->verified_at = null;
            }
        });
    }

    public function initializeHasVerification(): void
    {
        $this->mergeCasts([
            'status' => VerificationStatus::class,
            'verified_at' => 'datetime',
        ]);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', VerificationStatus::Terverifikasi->value);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', VerificationStatus::Menunggu->value);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isVerified(): bool
    {
        return $this->status === VerificationStatus::Terverifikasi;
    }
}
