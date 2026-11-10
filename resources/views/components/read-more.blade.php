@props(['text', 'lines' => 3])
{{-- Paragraf panjang dipotong; tombol "Selengkapnya" muncul hanya bila memang terpotong. --}}
<div class="read-more" data-read-more style="--lines: {{ $lines }}">
    <p class="read-more__text">{{ $text }}</p>
    <button type="button" class="read-more__btn" data-read-more-btn hidden aria-expanded="false">Selengkapnya</button>
</div>
