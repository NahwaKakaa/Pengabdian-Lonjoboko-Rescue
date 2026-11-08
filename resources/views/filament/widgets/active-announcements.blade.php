<x-filament-widgets::widget class="lr-panel-widget">
    <section class="lr-panel">
        <header class="lr-panel__head">
            <div>
                <h3 class="lr-panel__title">Informasi & peringatan</h3>
                <p class="lr-panel__sub">Yang terakhir diterbitkan untuk warga</p>
            </div>
            @if ($createUrl)
                <a href="{{ $createUrl }}" class="lr-mini-btn" wire:navigate>
                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"/></svg>
                    Buat
                </a>
            @endif
        </header>

        @if ($items->isEmpty())
            <div class="lr-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11v2a1 1 0 001 1h2l5 4V6L6 10H4a1 1 0 00-1 1zM15.5 8.5a5 5 0 010 7"/></svg>
                <p><strong>Belum ada informasi.</strong> Peringatan dari BMKG atau BPBD bisa diteruskan ke warga dari sini.</p>
            </div>
        @else
            <ul class="lr-list">
                @foreach ($items as $item)
                    <li>
                        <a href="{{ $item['url'] }}" class="lr-list__row" @if ($isAdmin) wire:navigate @else target="_blank" rel="noopener" @endif>
                            <span @class(['lr-tone', 'lr-tone--'.$item['tone'], 'is-live' => $item['active']]) aria-hidden="true"></span>
                            <span class="lr-list__body">
                                <span class="lr-list__title">{{ $item['title'] }}</span>
                                <span class="lr-list__meta">
                                    {{ $item['level']->getLabel() }} · {{ $item['when'] }}
                                    @if ($item['expired'])
                                        · sudah berakhir
                                    @endif
                                </span>
                            </span>
                            @if ($item['active'])
                                <span class="lr-pill is-{{ $item['tone'] }}">Berlaku</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
            @if ($indexUrl)
                <a href="{{ $indexUrl }}" class="lr-panel__foot" wire:navigate>Lihat semua informasi</a>
            @endif
        @endif
    </section>
</x-filament-widgets::widget>
