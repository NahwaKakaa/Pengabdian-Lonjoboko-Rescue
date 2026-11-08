<?php

namespace Database\Seeders;

use App\Enums\ContactCategory;
use App\Models\EmergencyContact;
use Illuminate\Database\Seeder;

/**
 * Nomor layanan darurat nasional. Kontak pemerintah desa, BPBD Gowa,
 * dan puskesmas setempat diisi admin lewat panel karena harus dipastikan
 * langsung ke instansinya.
 */
class EmergencyContactSeeder extends Seeder
{
    public function run(): void
    {
        $contacts = [
            ['name' => 'Ambulans / Gawat Darurat Medis (SPGDT)', 'category' => ContactCategory::Kesehatan, 'phone' => '119', 'description' => 'Layanan gawat darurat medis nasional.', 'sort_order' => 1],
            ['name' => 'Basarnas (Pencarian dan Pertolongan)', 'category' => ContactCategory::Keamanan, 'phone' => '115', 'description' => 'Untuk warga yang hanyut, tertimbun, atau terjebak.', 'sort_order' => 2],
            ['name' => 'Polisi', 'category' => ContactCategory::Keamanan, 'phone' => '110', 'description' => null, 'sort_order' => 3],
            ['name' => 'Pemadam Kebakaran', 'category' => ContactCategory::Keamanan, 'phone' => '113', 'description' => null, 'sort_order' => 4],
            ['name' => 'Panggilan Darurat Terpadu', 'category' => ContactCategory::Lainnya, 'phone' => '112', 'description' => 'Tersedia di banyak kabupaten/kota. Bila tidak tersambung, gunakan nomor lain.', 'sort_order' => 10],
        ];

        foreach ($contacts as $contact) {
            EmergencyContact::firstOrCreate(['phone' => $contact['phone'], 'name' => $contact['name']], $contact + ['is_active' => true]);
        }
    }
}
