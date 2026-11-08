<?php

namespace Tests\Feature;

use App\Enums\VerificationStatus;
use App\Models\EducationContent;
use App\Models\HazardZone;
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
