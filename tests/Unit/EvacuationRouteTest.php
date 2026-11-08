<?php

namespace Tests\Unit;

use App\Models\EducationContent;
use App\Models\EvacuationRoute;
use PHPUnit\Framework\TestCase;

class EvacuationRouteTest extends TestCase
{
    public function test_panjang_jalur_dihitung_dalam_meter(): void
    {
        // 0,001 derajat lintang ≈ 111 meter.
        $route = new EvacuationRoute(['path' => [[-5.280, 119.61], [-5.281, 119.61], [-5.282, 119.61]]]);

        $this->assertEqualsWithDelta(222, $route->lengthInMeters(), 2);
    }

    public function test_id_youtube_dikenali_dari_berbagai_format_tautan(): void
    {
        foreach ([
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ',
            'https://www.youtube.com/shorts/dQw4w9WgXcQ',
            'https://m.youtube.com/watch?feature=share&v=dQw4w9WgXcQ',
        ] as $url) {
            $this->assertSame('dQw4w9WgXcQ', (new EducationContent(['video_url' => $url]))->youtubeId(), $url);
        }

        $this->assertNull((new EducationContent(['video_url' => 'https://example.com/video']))->youtubeId());
    }
}
