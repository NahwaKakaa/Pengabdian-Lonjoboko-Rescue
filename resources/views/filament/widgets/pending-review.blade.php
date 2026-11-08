<x-filament-widgets::widget class="lr-panel-widget" id="menunggu-verifikasi">
    <section class="lr-panel">
        <header class="lr-panel__head">
            <div>
                <h3 class="lr-panel__title">Menunggu verifikasi</h3>
                <p class="lr-panel__sub">
                    @if ($total > 0)
                        {{ $total }} data peta belum tampil di web
                    @else
                        Tidak ada data yang menunggu
                    @endif
                </p>
            </div>
            @if ($total > 0)
                <span class="lr-count is-warn">{{ $total }}</span>
            @endif
        </header>

        @if ($items->isEmpty())
            <div class="lr-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
                <p><strong>Semua beres.</strong> Data dari kader akan muncul di sini untuk diverifikasi.</p>
            </div>
        @else
            <ul class="lr-list">
                @foreach ($items as $item)
                    <li>
                        <a href="{{ $item['url'] }}" class="lr-list__row" wire:navigate>
                            <span class="lr-tone lr-tone--{{ $item['tone'] }}" aria-hidden="true"></span>
                            <span class="lr-list__body">
                                <span class="lr-list__title">{{ $item['name'] }}</span>
                                <span class="lr-list__meta">{{ $item['type'] }} · oleh {{ $item['creator'] }} · {{ $item['when'] }}</span>
                            </span>
                            <span class="lr-list__cta">{{ $isAdmin ? 'Tinjau' : 'Lihat' }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-filament-widgets::widget>
