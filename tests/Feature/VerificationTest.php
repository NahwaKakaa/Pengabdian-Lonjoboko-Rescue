<?php

namespace Tests\Feature;

use App\Enums\VerificationStatus;
use App\Models\HazardZone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationTest extends TestCase
{
    use RefreshDatabase;

    private function zoneData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Tebing belakang sekolah',
            'hazard_type' => 'longsor',
            'risk_level' => 'tinggi',
            'location' => ['lat' => -5.28, 'lng' => 119.61],
        ], $overrides);
    }

    public function test_data_dari_kader_selalu_menunggu_verifikasi(): void
    {
        $kader = User::factory()->create();
        $this->actingAs($kader);

        $zone = HazardZone::create($this->zoneData(['status' => VerificationStatus::Terverifikasi]));

        $this->assertSame(VerificationStatus::Menunggu, $zone->fresh()->status);
        $this->assertSame($kader->id, $zone->fresh()->created_by);
    }

    public function test_perubahan_oleh_kader_mengembalikan_status_ke_menunggu(): void
    {
        $admin = User::factory()->admin()->create();
        $kader = User::factory()->create();

        $this->actingAs($admin);
        $zone = HazardZone::create($this->zoneData(['status' => VerificationStatus::Terverifikasi]));
        $this->assertTrue($zone->fresh()->isVerified());
        $this->assertSame($admin->id, $zone->fresh()->verified_by);

        $this->actingAs($kader);
        $zone->update(['name' => 'Tebing belakang sekolah (diperbarui)']);

        $zone->refresh();
        $this->assertSame(VerificationStatus::Menunggu, $zone->status);
        $this->assertNull($zone->verified_by);
    }

    public function test_hak_hapus_kader_terbatas(): void
    {
        $admin = User::factory()->admin()->create();
        $kader = User::factory()->create();
        $kaderLain = User::factory()->create();

        $this->actingAs($kader);
        $milikKader = HazardZone::create($this->zoneData());

        $this->assertTrue($kader->can('delete', $milikKader));
        $this->assertFalse($kaderLain->can('delete', $milikKader));
        $this->assertTrue($admin->can('delete', $milikKader));

        $this->actingAs($admin);
        $milikKader->update(['status' => VerificationStatus::Terverifikasi]);

        $this->assertFalse($kader->can('delete', $milikKader->fresh()));
    }

    public function test_kader_tidak_bisa_mengelola_konten_publik(): void
    {
        $kader = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->assertFalse($kader->can('viewAny', \App\Models\Announcement::class));
        $this->assertFalse($kader->can('viewAny', User::class));
        $this->assertTrue($admin->can('viewAny', \App\Models\Announcement::class));
    }

    public function test_panel_admin_meminta_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_akun_nonaktif_tidak_bisa_masuk_panel(): void
    {
        $nonaktif = User::factory()->create(['is_active' => false]);

        $this->actingAs($nonaktif)->get('/admin')->assertForbidden();
    }
}
