<?php

namespace App\Models;

use App\Enums\ReportKind;
use App\Enums\ReportStatus;
use App\Models\Concerns\HasLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Laporan kejadian dari warga lewat halaman /lapor.
 * Tidak tampil di web publik; dicek kader lalu disahkan admin desa.
 */
class IncidentReport extends Model
{
    use HasLocation;

    protected $fillable = [
        'kind', 'description', 'hamlet', 'landmark', 'location', 'photo',
        'reporter_name', 'reporter_phone', 'status',
        'check_note', 'admin_note', 'disaster_event_id', 'ip_hash',
    ];

    protected function casts(): array
    {
        return [
            'kind' => ReportKind::class,
            'status' => ReportStatus::class,
            'checked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $report) {
            $report->code ??= 'LPR-'.now('Asia/Makassar')->format('ymd').'-'.Str::upper(Str::random(4));
            $report->status ??= ReportStatus::Baru;
        });

        static::saving(function (self $report) {
            $user = auth()->user();

            // Hanya admin desa yang boleh mengesahkan atau menolak laporan.
            if ($user && ! $user->isAdmin() && in_array($report->status, [ReportStatus::Sah, ReportStatus::TidakSesuai], true)) {
                $report->status = $report->getOriginal('status') ?? ReportStatus::Dicek;
            }

            if ($report->isDirty('status') && $report->status === ReportStatus::Dicek && $user) {
                $report->checked_by = $user->id;
                $report->checked_at = now();
            }
        });
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function disasterEvent(): BelongsTo
    {
        return $this->belongsTo(DisasterEvent::class);
    }
}
