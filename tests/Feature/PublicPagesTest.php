<?php

namespace Tests\Feature;

use App\Enums\VerificationStatus;
use App\Enums\ContactCategory;
use App\Models\EducationContent;
use App\Models\EmergencyContact;
use App\Models\HazardZone;
use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoMapSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_semua_halaman_publik_bisa_dibuka_walau_data_kosong(): void
    {
        foreach (['home', 'risk-map', 'evacuation', 'education', 'emergency', 'volunteers'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_halaman_publik_bisa_dibuka_dengan_data_awal_dan_contoh(): void
    {
        $this->seed([DatabaseSeeder::class, DemoMapSeeder::class]);

        foreach (['home', 'risk-map', 'evacuation', 'education', 'emergency', 'volunteers'] as $route) {
            $this->get(route($route))->assertOk();
        }

        $content = EducationContent::first();
        $this->get(route('education.show', $content))->assertOk()->assertSee($content->title);
    }

    public function test_footer_menampilkan_whatsapp_kontak_darurat_dan_peta(): void
    {
        $this->seed(DatabaseSeeder::class);
        Setting::putMany(['whatsapp_number' => '0812-3456-7890', 'whatsapp_label' => 'Posko Desa']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('https://wa.me/6281234567890', false)
            ->assertSee('Posko Desa')
            ->assertSee('Kontak Darurat')
            ->assertSee('tel:119', false)
            ->assertSee('maps.google.com/maps?q=', false);
    }

    public function test_admin_bisa_memilih_kontak_yang_tampil_di_footer(): void
    {
        EmergencyContact::create(['name' => 'Posko Kader Tangguh', 'category' => ContactCategory::Desa, 'phone' => '081200000001', 'is_active' => true, 'show_in_footer' => true]);
        EmergencyContact::create(['name' => 'Kontak Khusus Info Darurat', 'category' => ContactCategory::Desa, 'phone' => '081200000002', 'is_active' => true, 'show_in_footer' => false]);
        EmergencyContact::create(['name' => 'Kontak Nonaktif', 'category' => ContactCategory::Desa, 'phone' => '081200000003', 'is_active' => false, 'show_in_footer' => true]);

        // Halaman Relawan tidak menampilkan daftar kontak, jadi yang terlihat hanya isi footer.
        $this->get(route('volunteers'))
            ->assertOk()
            ->assertSee('Posko Kader Tangguh')
            ->assertDontSee('Kontak Khusus Info Darurat')
            ->assertDontSee('Kontak Nonaktif');

        // Di halaman Info Darurat, kontak aktif tetap tampil walau tidak dipilih untuk footer.
        $this->get(route('emergency'))->assertSee('Kontak Khusus Info Darurat');
    }

    public function test_hanya_data_terverifikasi_yang_tampil_di_peta(): void
    {
        HazardZone::create([
            'name' => 'Lokasi Sudah Diverifikasi',
            'hazard_type' => 'banjir',
            'risk_level' => 'tinggi',
            'location' => ['lat' => -5.28, 'lng' => 119.61],
            'status' => VerificationStatus::Terverifikasi,
        ]);

        HazardZone::create([
            'name' => 'Lokasi Belum Diverifikasi',
            'hazard_type' => 'longsor',
            'risk_level' => 'sedang',
            'location' => ['lat' => -5.29, 'lng' => 119.62],
            'status' => VerificationStatus::Menunggu,
        ]);

        $this->get(route('risk-map'))
            ->assertOk()
            ->assertSee('Lokasi Sudah Diverifikasi')
            ->assertDontSee('Lokasi Belum Diverifikasi');
    }

    public function test_konten_edukasi_yang_disembunyikan_tidak_bisa_dibuka(): void
    {
        $content = EducationContent::create([
            'title' => 'Draf modul',
            'category' => 'modul',
            'topic' => 'umum',
            'is_published' => false,
        ]);

        $this->get(route('education.show', $content))->assertNotFound();
    }
}
