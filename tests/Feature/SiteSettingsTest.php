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

    public function test_isi_dan_hapus_data_desa(): void
    {
        $this->artisan('lonjoboko:isi-data-desa')->assertSuccessful();

        $this->assertSame(6, \App\Models\AssemblyPoint::count());
        $this->assertSame(0, \App\Models\HazardZone::where('name', 'like', '[CONTOH]%')->count());
        $this->assertDatabaseHas('emergency_contacts', ['name' => 'BPBD Kabupaten Gowa']);

        // Tampil di halaman publik, laporan yang belum diverifikasi tidak.
        $this->get('/peta-risiko')->assertOk()
            ->assertSee("Lereng Jalan Poros Malino, Mala'lang")
            ->assertDontSee('Lereng kebun di utara Tombongi');
        $this->get('/jalur-evakuasi')->assertOk()->assertSee('Masjid Ar Rahman');

        // Tidak mengisi dua kali tanpa --ulang.
        $this->artisan('lonjoboko:isi-data-desa')->assertFailed();

        $this->artisan('lonjoboko:hapus-data-desa')->assertSuccessful();
        $this->assertSame(0, \App\Models\AssemblyPoint::count());
        $this->assertSame(0, \App\Models\EvacuationRoute::count());
        $this->assertDatabaseHas('emergency_contacts', ['name' => 'BPBD Kabupaten Gowa']);
    }

    public function test_lengkapi_profil_dan_edukasi_tanpa_menimpa_isian_admin(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        \App\Models\Setting::putMany(['office_phone' => '0411-000', 'whatsapp_number' => '081234567890']);

        $this->artisan('lonjoboko:lengkapi-data')->assertSuccessful();

        $this->assertSame('081234567890', \App\Models\Setting::get('whatsapp_number'));
        $this->assertSame('Posko Desa Lonjoboko', \App\Models\Setting::get('whatsapp_label'));
        $this->assertNotEmpty(\App\Models\Setting::get('village_profile'));

        $this->assertDatabaseHas('education_contents', ['title' => 'Sungai Jeneberang dan risiko banjir di Lonjoboko']);
        $infographic = \App\Models\EducationContent::where('title', 'Infografis: isi tas siaga bencana')->firstOrFail();
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($infographic->image);

        // Dijalankan lagi tidak membuat duplikat.
        $count = \App\Models\EducationContent::count();
        $this->artisan('lonjoboko:lengkapi-data')->assertSuccessful();
        $this->assertSame($count, \App\Models\EducationContent::count());

        $this->get('/edukasi')->assertOk()->assertSee('Aman melintas di Jalan Poros Malino')->assertSee('SiAGA Tanah Longsor');
        $this->assertSame('JFK7A1n6JJI', \App\Models\EducationContent::where('title', 'SiAGA Tanah Longsor')->firstOrFail()->youtubeId());
        $this->get('/relawan')->assertOk()->assertSee('Lima tim siaga dan tugasnya');

        $this->artisan('lonjoboko:hapus-data-desa')->assertSuccessful();
        $this->assertSame(0, \App\Models\EducationContent::count());
    }

    public function test_foto_unggahan_memakai_https_walau_app_url_masih_http(): void
    {
        config(['app.url' => 'http://lonjoboko-rescue.web.id']);

        AppServiceProvider::useRequestScheme(\Illuminate\Http\Request::create('https://lonjoboko-rescue.web.id/'));
        $this->assertSame('https://lonjoboko-rescue.web.id/storage/desa/foto.jpg', \Illuminate\Support\Facades\Storage::disk('public')->url('desa/foto.jpg'));

        // Di laptop (http) tetap http.
        \Illuminate\Support\Facades\URL::forceScheme(null);
        AppServiceProvider::useRequestScheme(\Illuminate\Http\Request::create('http://127.0.0.1:8000/'));
        $this->assertSame('http://127.0.0.1:8000/storage/desa/foto.jpg', \Illuminate\Support\Facades\Storage::disk('public')->url('desa/foto.jpg'));
    }
}
