<?php

namespace Tests\Feature;

use App\Enums\HazardType;
use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use App\Mail\NewIncidentReport;
use App\Models\Activity;
use App\Models\DisasterEvent;
use App\Models\Facility;
use App\Models\HamletPopulation;
use App\Models\IncidentReport;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Data penduduk, riwayat kejadian, laporan warga, kegiatan Karang Taruna, faskes. */
class VillageRecordsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_warga_bisa_melapor_tanpa_akun(): void
    {
        Storage::fake('public');

        $this->get(route('report'))->assertOk()->assertSee('Laporkan kejadian atau tanda bahaya');

        $this->post(route('report.store'), [
            'kind' => 'tanda_bahaya',
            'description' => 'Retakan tanah baru di kebun setelah hujan semalam.',
            'hamlet' => 'Tombongi',
            'landmark' => 'dekat Masjid Ar Rahman',
            'lat' => '-5.265100',
            'lng' => '119.708200',
            'photo' => UploadedFile::fake()->image('retak.jpg', 800, 600),
            'reporter_phone' => '0812 3456 7890',
        ])->assertRedirect(route('report'))->assertSessionHas('reported');

        $report = IncidentReport::firstOrFail();
        $this->assertSame(ReportStatus::Baru, $report->status);
        $this->assertStringStartsWith('LPR-', $report->code);
        $this->assertSame(['lat' => -5.2651, 'lng' => 119.7082], $report->location);
        Storage::disk('public')->assertExists($report->photo);

        // Laporan warga tidak tampil di web publik.
        $this->get(route('history'))->assertDontSee('Retakan tanah baru');
    }

    public function test_laporan_kosong_ditolak_dan_robot_diabaikan(): void
    {
        $this->post(route('report.store'), ['kind' => '', 'description' => ''])
            ->assertSessionHasErrors(['kind', 'description']);

        $this->post(route('report.store'), [
            'kind' => 'banjir',
            'description' => 'Pesan dari robot yang mengisi semua kolom.',
            'website' => 'http://spam.example',
        ])->assertRedirect(route('report'));

        $this->assertSame(0, IncidentReport::count());
    }

    public function test_kader_mengecek_laporan_tapi_hanya_admin_yang_mengesahkan(): void
    {
        $kader = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $report = IncidentReport::create(['kind' => 'longsor', 'description' => 'Tanah turun ke jalan dekat jembatan.']);

        $this->actingAs($kader)->get('/admin/incident-reports')->assertOk()->assertSee($report->code);

        $this->actingAs($kader);
        $report->update(['status' => ReportStatus::Dicek, 'check_note' => 'Benar, timbunan setinggi 1 m.']);
        $this->assertSame($kader->id, $report->fresh()->checked_by);

        $report->update(['status' => ReportStatus::Sah]);
        $this->assertSame(ReportStatus::Dicek, $report->fresh()->status);

        $this->actingAs($admin);
        $report->update(['status' => ReportStatus::Sah]);
        $this->assertSame(ReportStatus::Sah, $report->fresh()->status);
    }

    public function test_riwayat_kejadian_hanya_menampilkan_yang_terverifikasi(): void
    {
        DisasterEvent::create([
            'title' => 'Longsor di tebing jalan poros', 'hazard_type' => HazardType::Longsor,
            'occurred_on' => '2022-11-16', 'deaths' => 5, 'missing' => 2, 'houses_damaged' => 5,
            'status' => VerificationStatus::Terverifikasi,
        ]);
        DisasterEvent::create([
            'title' => 'Usulan kader belum dicek', 'hazard_type' => HazardType::Banjir,
            'occurred_on' => '2026-11-01', 'status' => VerificationStatus::Menunggu,
        ]);

        $this->get(route('history'))
            ->assertOk()
            ->assertSee('Longsor di tebing jalan poros')
            ->assertSee('Nov 2022')
            ->assertDontSee('Usulan kader belum dicek');

        $this->get(route('risk-map'))->assertSee('Longsor di tebing jalan poros');
    }

    public function test_data_penduduk_dan_warga_rentan_tampil_sebagai_jumlah(): void
    {
        HamletPopulation::create(['name' => 'Tombongi', 'population' => 754, 'households' => 210, 'elderly' => 67, 'toddlers' => 60, 'location' => ['lat' => -5.265, 'lng' => 119.7085]]);
        HamletPopulation::create(['name' => 'Bontoloe', 'population' => 812, 'households' => 231, 'elderly' => 73]);

        $this->get(route('home'))->assertSee('1.566')->assertSee('Warga rentan');
        $this->get(route('risk-map'))
            ->assertSee('Siapa yang perlu didahulukan saat evakuasi')
            ->assertSee('Tombongi')
            ->assertSee('"vulnerable":127', false);

        $kader = User::factory()->create();
        $this->actingAs($kader)->get('/admin/hamlet-populations')->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get('/admin/hamlet-populations')->assertOk();
    }

    public function test_kegiatan_karang_taruna_dan_jam_layanan_faskes(): void
    {
        $kader = User::factory()->create();
        $this->actingAs($kader);
        Activity::create(['title' => 'Simulasi evakuasi longsor', 'held_on' => '2026-11-01', 'place' => 'Kantor Desa']);
        Activity::create(['title' => 'Draf belum tampil', 'held_on' => '2026-11-02', 'is_published' => false]);
        auth()->logout();

        $this->get(route('volunteers'))->assertSee('Simulasi evakuasi longsor')->assertDontSee('Draf belum tampil');

        Facility::create([
            'name' => 'Puskesmas Parangloe', 'category' => 'kesehatan', 'phone' => '0411-000000',
            'opening_hours' => 'Senin–Sabtu 08.00–14.00', 'location' => ['lat' => -5.247, 'lng' => 119.651],
            'status' => VerificationStatus::Terverifikasi,
        ]);

        $this->get(route('emergency'))->assertSee('Fasilitas kesehatan')->assertSee('Senin–Sabtu 08.00–14.00');
        $this->actingAs($kader)->get('/admin/activities')->assertOk();
    }

    public function test_detail_kegiatan_menampilkan_foto_dan_bisa_dibuka_dari_kartu(): void
    {
        $activity = Activity::create([
            'title' => 'Simulasi evakuasi longsor', 'held_on' => '2026-11-01', 'place' => 'Kantor Desa',
            'description' => 'Warga berlatih menuju titik kumpul.',
            'photo' => 'kegiatan/a.jpg', 'gallery' => ['kegiatan/b.jpg', 'kegiatan/c.jpg'],
        ]);
        $draft = Activity::create(['title' => 'Draf', 'held_on' => '2026-11-02', 'is_published' => false]);

        $this->get(route('volunteers'))
            ->assertSee(route('activity.show', $activity))
            ->assertSee('3 foto');

        $this->get(route('activity.show', $activity))
            ->assertOk()
            ->assertSee('Simulasi evakuasi longsor')
            ->assertSee('Warga berlatih menuju titik kumpul.')
            ->assertSee('kegiatan/c.jpg');

        $this->get(route('activity.show', $draft))->assertNotFound();
    }

    public function test_laporan_dikirim_ke_email_dan_muncul_tombol_wa_posko(): void
    {
        Mail::fake();
        Storage::fake('public');
        Setting::putMany(['report_emails' => 'desa@contoh.test, kt@contoh.test, bukan-email', 'whatsapp_number' => '0812 3456 7890']);

        $this->followingRedirects()->post(route('report.store'), [
            'kind' => 'longsor',
            'description' => 'Tanah tebing jatuh ke jalan dekat jembatan.',
            'hamlet' => 'Galesong',
            'lat' => '-5.2655', 'lng' => '119.7224',
            'photo' => UploadedFile::fake()->image('tebing.jpg'),
        ])->assertOk()
            ->assertSee('Kirim juga ke WA Posko')
            ->assertSee('https://wa.me/6281234567890?text=', false);

        $report = IncidentReport::firstOrFail();
        Mail::assertSent(NewIncidentReport::class, function (NewIncidentReport $mail) use ($report) {
            $mail->assertSeeInHtml($report->code);
            $mail->assertSeeInHtml('Galesong');

            return $mail->hasTo('desa@contoh.test') && $mail->hasTo('kt@contoh.test')
                && ! $mail->hasTo('bukan-email') && count($mail->attachments()) === 1;
        });
    }

    public function test_tanpa_email_penerima_tidak_ada_email(): void
    {
        Mail::fake();

        $this->post(route('report.store'), ['kind' => 'banjir', 'description' => 'Air sungai naik ke jalan dusun.']);

        $this->assertSame(1, IncidentReport::count());
        Mail::assertNothingSent();
    }
}
