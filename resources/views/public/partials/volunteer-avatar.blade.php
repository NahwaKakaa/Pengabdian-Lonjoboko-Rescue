{{-- Foto relawan, atau inisial nama bila belum ada foto --}}
<span class="vol-avatar vol-avatar--{{ $size }}" aria-hidden="true">
    @if ($person->photo)
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($person->photo) }}" alt="" loading="lazy">
    @else
        <span class="vol-avatar__initials">{{ $person->initials() }}</span>
    @endif
</span>
