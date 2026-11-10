{{--
    Ringkasan penduduk dan warga rentan.
    $hamlets  : koleksi HamletPopulation
    $detailed : true = tampilkan tabel per dusun (halaman Peta Risiko)
--}}
@php
    $totals = \App\Models\HamletPopulation::totals($hamlets);
    $vulnerable = collect(\App\Models\HamletPopulation::VULNERABLE)
        ->map(fn ($label, $col) => ['label' => $label, 'value' => $totals[$col]])
        ->filter(fn ($row) => $row['value'] !== null);
    $vulnerableSum = $vulnerable->sum('value');
    $fmt = fn ($n) => $n === null ? '–' : number_format($n, 0, ',', '.');
    $source = \App\Models\Setting::get('population_source');
@endphp

@if ($hamlets->isNotEmpty())
    <div class="pop">
        <dl class="pop__stats">
            <div class="pop__stat">
                <dt>Jumlah penduduk</dt>
                <dd class="num">{{ $fmt($totals['population']) }} <span>jiwa</span></dd>
            </div>
            <div class="pop__stat">
                <dt>Kepala keluarga</dt>
                <dd class="num">{{ $fmt($totals['households']) }} <span>KK</span></dd>
            </div>
            <div class="pop__stat pop__stat--warn">
                <dt>Warga rentan</dt>
                <dd class="num">{{ $vulnerable->isEmpty() ? '–' : $fmt($vulnerableSum) }} <span>orang</span></dd>
            </div>
        </dl>

        @if ($vulnerable->isNotEmpty())
            <ul class="pop__chips" aria-label="Rincian warga rentan">
                @foreach ($vulnerable as $row)
                    <li><span>{{ $row['label'] }}</span> <strong class="num">{{ $fmt($row['value']) }}</strong></li>
                @endforeach
            </ul>
        @endif

        @if ($detailed ?? false)
            {{-- HP: satu kartu per dusun, tanpa geser ke samping --}}
            <ul class="pop__cards">
                @foreach ($hamlets as $h)
                    <li class="pop__card">
                        <div class="pop__card-head">
                            <h3>Dusun {{ $h->name }}</h3>
                            <p class="num">{{ $fmt($h->population) }} jiwa · {{ $fmt($h->households) }} KK</p>
                        </div>
                        <dl class="pop__card-grid">
                            @foreach (\App\Models\HamletPopulation::VULNERABLE as $col => $label)
                                <div>
                                    <dt>{{ $label }}</dt>
                                    <dd class="num">{{ $fmt($h->{$col}) }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        @if ($h->note)
                            <p class="pop__card-note">{{ $h->note }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>

            <div class="pop__table-wrap">
                <table class="pop__table">
                    <thead>
                        <tr>
                            <th scope="col">Dusun</th>
                            <th scope="col">Penduduk</th>
                            <th scope="col">KK</th>
                            @foreach (\App\Models\HamletPopulation::VULNERABLE as $label)
                                <th scope="col">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hamlets as $h)
                            <tr>
                                <th scope="row">{{ $h->name }}</th>
                                <td class="num">{{ $fmt($h->population) }}</td>
                                <td class="num">{{ $fmt($h->households) }}</td>
                                @foreach (array_keys(\App\Models\HamletPopulation::VULNERABLE) as $col)
                                    <td class="num">{{ $fmt($h->{$col}) }}</td>
                                @endforeach
                            </tr>
                            @if ($h->note)
                                <tr class="pop__note-row"><td colspan="7">{{ $h->note }}</td></tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <p class="pop__source">
            @if ($source) Sumber: {{ $source }}. @endif
            Hanya jumlah, tanpa nama atau alamat warga.
        </p>
    </div>
@elseif ($detailed ?? false)
    <p class="rounded-2xl border border-dashed border-line bg-card p-5 text-sm text-ink-soft">Data penduduk per dusun belum diisi oleh pemerintah desa.</p>
@endif
