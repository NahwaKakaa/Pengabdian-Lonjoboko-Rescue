<?php

namespace Tests\Unit;

use App\Support\Phone;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    public function test_nomor_08_diubah_ke_format_62_untuk_whatsapp(): void
    {
        $this->assertSame('https://wa.me/6281234567890', Phone::whatsappHref('0812-3456-7890'));
        $this->assertSame('https://wa.me/6281234567890', Phone::whatsappHref('+62 812 3456 7890'));
    }

    public function test_pesan_whatsapp_ikut_dikodekan(): void
    {
        $this->assertSame('https://wa.me/628123?text=Halo%20desa', Phone::whatsappHref('08123', 'Halo desa'));
    }

    public function test_nomor_kosong_tidak_menghasilkan_tautan(): void
    {
        $this->assertNull(Phone::whatsappHref(null));
        $this->assertNull(Phone::whatsappHref(''));
    }

    public function test_tautan_telepon_hanya_berisi_angka_dan_plus(): void
    {
        $this->assertSame('tel:+62411123', Phone::telHref('+62 (411) 123'));
    }
}
