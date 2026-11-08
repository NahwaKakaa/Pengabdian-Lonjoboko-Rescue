<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Setting;
use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Support\VillageBoundary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Data yang diatur admin (Profil Desa, Peta & Sumber Resmi) tampil di web publik. */
class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_halaman_pengaturan_bisa_dibuka_admin_saja(): void
    {
        $admin = User::factory()->admin()->create();
        $kader = User::factory()->create();

        $this->actingAs($admin)->get('/admin/village-profile')->assertOk()->assertSee('Panduan keselamatan');
        $this->actingAs($admin)->get('/admin/map-settings')->assertOk()->assertSee('Batas wilayah desa');

        $this->actingAs($kader)->get('/admin/map-settings')->assertForbidden();
    }

    public function test_panduan_keselamatan_bawaan_tampil_bila_belum_diisi(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('Selamatkan diri, bukan barang');
        $this->get(route('evacuation'))->assertOk()->assertSee('Jauhi tepi sungai');
    }

    public function test_panduan_keselamatan_dari_admin_dipakai_di_semua_tempat(): void
    {
        Setting::putMany([
            'safety_first_steps' => json_encode([
                ['title' => 'Bunyikan kentongan', 'body' => 'Tiga kali berturut-turut.'],
                ['title' => 'Bantu lansia', 'body' => ''],
                ['title' => 'Kumpul di masjid', 'body' => ''],
            ]),
            'safety_flood_steps' => "1. Naik ke rumah panggung tetangga\n- Matikan listrik",
            'safety_landslide_steps' => 'Menjauh dari tebing Bontote\'ne',
            'safety_popup_message' => 'Segera ke Masjid Nurul Huda.',
        ]);

        $this->get(route('home'))->assertSee('Bunyikan kentongan')->assertDontSee('Selamatkan diri, bukan barang');
        $this->get(route('evacuation'))
            ->assertSee('Naik ke rumah panggung tetangga')
            ->assertDontSee('1. Naik', false)
            ->assertSee("Menjauh dari tebing Bontote'ne");
        $this->get(route('poster'))->assertSee('Matikan listrik');

        Announcement::create(['title' => 'Banjir besar', 'level' => 'darurat', 'body' => 'Air naik.', 'is_published' => true]);
        $this->get(route('home'))->assertSee('Segera ke Masjid Nurul Huda.');
    }

    public function test_foto_desa_dari_admin_menggantikan_ilustrasi(): void
    {
        Setting::putMany([
            'photo_hero' => 'desa/latar.jpg',
            'photo_profile' => 'desa/sawah.jpg',
            'photo_profile_caption' => 'Sawah Dusun Bontote\'ne',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('desa/latar.jpg', false)
            ->assertSee('has-hero-photo', false)
            ->assertSee('desa/sawah.jpg', false)
            ->assertSee('Foto desa')
            ->assertSee("Sawah Dusun Bontote'ne");
    }

    public function test_batas_desa_dan_sumber_resmi_dari_admin(): void
    {
        $this->assertFalse(VillageBoundary::isCustom());

        Setting::putMany([
            'village_boundary' => json_encode([[-5.26, 119.72], [-5.26, 119.74], [-5.28, 119.74]]),
            'official_sources' => json_encode([['name' => 'BPBD Kabupaten Gowa', 'url' => 'https://bpbd.gowakab.go.id', 'description' => 'Posko kabupaten']]),
            'map_zoom' => '15',
        ]);

        $this->assertTrue(VillageBoundary::isCustom());
        $this->assertSame([[-5.26, 119.72], [-5.26, 119.74], [-5.28, 119.74]], VillageBoundary::ring());

        // Pengaturan peta dibaca saat aplikasi mulai; jalankan ulang di sini.
        AppServiceProvider::applyMapSettings();

        $this->get(route('risk-map'))->assertOk()->assertSee('119.74', false)->assertSee('Diatur pengelola desa');
        $this->get(route('emergency'))->assertOk()->assertSee('BPBD Kabupaten Gowa')->assertDontSee('InaRISK BNPB');
        $this->assertSame(15, config('lonjoboko.map.zoom'));
    }

    public function test_halaman_masuk_admin_memakai_tampilan_desa(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Masuk ke panel desa')
            ->assertSee('Kabar bencana desa, dikelola dari satu tempat.')
            ->assertSee('Tidak ada peringatan berlaku')
            ->assertSee('lr-auth-page');
    }
}
