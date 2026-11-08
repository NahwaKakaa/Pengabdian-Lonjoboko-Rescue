{{-- Bagian bawah sidebar admin: tautan ke web publik. --}}
<div class="lr-side-foot" x-show="$store.sidebar.isOpen" x-transition.opacity>
    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="lr-side-foot__link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3"/></svg>
        <span>
            <strong>Lihat web publik</strong>
            <small>{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'lonjoboko-rescue.web.id' }}</small>
        </span>
    </a>
</div>
