<?php

namespace App\Mail;

use App\Filament\Resources\IncidentReportResource;
use App\Models\IncidentReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/** Pemberitahuan email ke pengelola desa saat warga mengirim laporan lewat /lapor. */
class NewIncidentReport extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public IncidentReport $report) {}

    public function envelope(): Envelope
    {
        $where = $this->report->hamlet ? ' di '.$this->report->hamlet : '';

        return new Envelope(
            subject: "Laporan warga baru: {$this->report->kind->getLabel()}{$where} ({$this->report->code})",
        );
    }

    public function content(): Content
    {
        $location = $this->report->location;

        return new Content(
            markdown: 'mail.new-incident-report',
            with: [
                'report' => $this->report,
                'adminUrl' => IncidentReportResource::getUrl('edit', ['record' => $this->report], panel: 'admin'),
                'mapsUrl' => $location ? "https://www.google.com/maps?q={$location['lat']},{$location['lng']}" : null,
            ],
        );
    }

    /** @return list<Attachment> */
    public function attachments(): array
    {
        $photo = $this->report->photo;

        if (! $photo || ! Storage::disk('public')->exists($photo)) {
            return [];
        }

        return [Attachment::fromStorageDisk('public', $photo)->as($this->report->code.'.'.pathinfo($photo, PATHINFO_EXTENSION))];
    }
}
