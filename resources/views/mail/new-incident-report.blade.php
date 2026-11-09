<x-mail::message>
# Ada laporan warga baru

**{{ $report->kind->getLabel() }}**@if ($report->hamlet) di **Dusun {{ $report->hamlet }}**@endif

<x-mail::panel>
{{ $report->description }}
</x-mail::panel>

<x-mail::table>
| | |
|:--|:--|
| Nomor laporan | {{ $report->code }} |
| Waktu | {{ $report->created_at->timezone('Asia/Makassar')->translatedFormat('l, j F Y · H.i') }} WITA |
| Dusun | {{ $report->hamlet ?: '-' }} |
| Patokan | {{ $report->landmark ?: '-' }} |
| Pelapor | {{ $report->reporter_name ?: 'Tidak disebutkan' }} |
| Nomor HP | {{ $report->reporter_phone ?: '-' }} |
| Foto | {{ $report->photo ? 'Terlampir di email ini' : 'Tidak ada' }} |
</x-mail::table>

@if ($mapsUrl)
Lokasi GPS dari pelapor: [buka di Google Maps]({{ $mapsUrl }})
@endif

<x-mail::button :url="$adminUrl" color="success">
Buka laporan di admin
</x-mail::button>

Langkah berikutnya: kader Karang Taruna mengecek laporan (bila aman ke lokasi), lalu pemerintah desa mengesahkannya.
Bila ada korban, hubungi Ambulans 119 atau Basarnas 115.

Email otomatis dari {{ config('app.name') }}.
</x-mail::message>
