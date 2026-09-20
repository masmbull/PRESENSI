@extends('layouts.admin')

@php
    $dayLabel = \Illuminate\Support\Carbon::parse($stats['date'])->locale('id')->translatedFormat('l, d F Y');
@endphp

@section('title', 'Ringkasan')
@section('heading', 'Ringkasan Presensi Hari Ini')
@section('sub', $dayLabel.' · zona waktu '.config('app.timezone').' · data tersimpan otomatis dari halaman absen SPG')

@section('actions')
  <a class="btn" href="{{ route('admin.absensi') }}">Riwayat absen &amp; export →</a>
  <a class="btn ghost" href="{{ route('admin.dashboard') }}">Muat ulang</a>
@endsection

@push('head')
<style>
.kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(158px,1fr));gap:10px;margin-bottom:14px}
.kpi{background:var(--card);border:1px solid var(--line);border-radius:var(--r2);padding:13px 14px}
.klab{font-size:9.5px;letter-spacing:.12em;text-transform:uppercase;color:var(--dim)}
.kval{display:block;font-size:26px;font-family:var(--mono);margin:5px 0 2px;letter-spacing:-.02em}
.ksub{font-size:10.5px;color:var(--dim)}
.k-acc .kval{color:var(--acc)} .k-sky .kval{color:var(--sky)} .k-mid .kval{color:var(--mid)}
.k-warn .kval{color:var(--warn)} .k-vio .kval{color:var(--vio)} .k-txt .kval{color:var(--txt)}
.chart{display:flex;gap:7px;align-items:flex-end;height:186px;overflow-x:auto;padding:6px 2px 0}
.day{flex:1 0 30px;display:flex;flex-direction:column;align-items:center;gap:6px;height:100%;justify-content:flex-end}
.bars{display:flex;gap:3px;align-items:flex-end;height:100%;width:100%;justify-content:center}
.bar{width:10px;border-radius:4px 4px 0 0;min-height:3px;transition:filter .15s}
.bar:hover{filter:brightness(1.25)}
.bar.m{background:linear-gradient(180deg,#34d399,#0ea56b)}
.bar.p{background:linear-gradient(180deg,#7dd3fc,#0284c7)}
.bar.zero{background:rgba(148,178,214,.18)}
.dlab{font-size:9.5px;color:var(--dim);font-family:var(--mono)}
.legend{display:flex;gap:14px;font-size:11px;color:var(--dim);align-items:center}
.dot{width:9px;height:9px;border-radius:3px;display:inline-block;margin-right:5px;vertical-align:middle}
.cols{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media (max-width:1100px){.cols{grid-template-columns:1fr}}
.tiny{font-size:11px;color:var(--dim)}
</style>
@endpush

@section('content')
<div class="kpis">
  <div class="kpi k-acc"><span class="klab">Absen masuk</span><b class="kval">{{ $stats['masuk'] }}</b><span class="ksub">record hari ini</span></div>
  <div class="kpi k-sky"><span class="klab">Absen pulang</span><b class="kval">{{ $stats['pulang'] }}</b><span class="ksub">record hari ini</span></div>
  <div class="kpi k-txt"><span class="klab">Orang hadir</span><b class="kval">{{ $stats['orang_hadir'] }}</b><span class="ksub">SPG unik absen masuk</span></div>
  <div class="kpi k-mid"><span class="klab">Belum pulang</span><b class="kval">{{ $stats['belum_pulang'] }}</b><span class="ksub">sudah masuk, belum pulang</span></div>
  <div class="kpi k-warn"><span class="klab">Di luar radius</span><b class="kval">{{ $stats['luar_radius'] }}</b><span class="ksub">record di luar geofence</span></div>
  <div class="kpi k-sky"><span class="klab">Toko aktif</span><b class="kval">{{ $stats['stores_aktif'] }}</b><span class="ksub">dari {{ $stats['cities_aktif'] }} daerah</span></div>
  <div class="kpi k-vio"><span class="klab">Total record</span><b class="kval">{{ $stats['total'] }}</b><span class="ksub">masuk + pulang hari ini</span></div>
</div>

<section class="card">
  <h2>
    <span>Tren 14 hari terakhir (masuk / pulang)</span>
    <span class="legend"><span><i class="dot" style="background:#34d399"></i>masuk</span><span><i class="dot" style="background:#7dd3fc"></i>pulang</span></span>
  </h2>
  <div class="chart">
    @foreach ($trend as $t)
    <div class="day" title="{{ $t['label'] }} — masuk {{ $t['masuk'] }}, pulang {{ $t['pulang'] }}">
      <div class="bars">
        <div class="bar m {{ $t['masuk'] ? '' : 'zero' }}" style="height:{{ $t['masuk'] ? max(3, $t['h_masuk']) : 3 }}%"></div>
        <div class="bar p {{ $t['pulang'] ? '' : 'zero' }}" style="height:{{ $t['pulang'] ? max(3, $t['h_pulang']) : 3 }}%"></div>
      </div>
      <span class="dlab">{{ $t['label'] }}</span>
    </div>
    @endforeach
  </div>
</section>

<section class="card">
  <h2>
    <span>Rekap per karyawan hari ini ({{ $perEmployee->count() }} orang)</span>
    <a class="tiny" href="{{ route('admin.absensi') }}">lihat detail per record →</a>
  </h2>
  @if ($perEmployee->isEmpty())
    <div class="empty">Belum ada absen hari ini. Data dari HP SPG otomatis muncul di sini.</div>
  @else
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Nama</th><th>Kode</th><th>Toko</th><th>Daerah</th><th>Masuk</th><th>Pulang</th><th>Status</th></tr>
      </thead>
      <tbody>
        @foreach ($perEmployee as $p)
        <tr>
          <td><b>{{ $p['name'] }}</b></td>
          <td class="mono dim">{{ $p['code'] ?? '—' }}</td>
          <td>{{ $p['store'] ?? '—' }}</td>
          <td class="dim">{{ $p['city'] ?? '—' }}</td>
          <td class="mono">{{ $p['masuk'] ?? '—' }}</td>
          <td class="mono">{{ $p['pulang'] ?? '—' }}</td>
          <td>
            @if ($p['pulang'])
              <span class="pill p-ok">lengkap</span>
            @else
              <span class="pill p-mid">belum pulang</span>
            @endif
            @if ($p['luar_radius'] > 0) <span class="pill p-warn">{{ $p['luar_radius'] }}x luar radius</span> @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</section>

<div class="cols">
  <section class="card">
    <h2><span>Per toko (hari ini)</span></h2>
    @if ($perStore->isEmpty())
      <div class="empty">Belum ada aktivitas toko hari ini.</div>
    @else
    <div class="table-wrap">
      <table>
        <thead><tr><th>Toko</th><th>Daerah</th><th>Orang</th><th>Masuk</th><th>Pulang</th></tr></thead>
        <tbody>
          @foreach ($perStore as $s)
          <tr>
            <td><b>{{ $s['store'] }}</b></td>
            <td class="dim">{{ $s['city'] }}</td>
            <td class="mono">{{ $s['orang'] }}</td>
            <td class="mono" style="color:#6ee7b7">{{ $s['masuk'] }}</td>
            <td class="mono" style="color:#7dd3fc">{{ $s['pulang'] }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </section>

  <section class="card">
    <h2><span>Per daerah / kota (hari ini)</span></h2>
    @if ($perCity->isEmpty())
      <div class="empty">Belum ada aktivitas daerah hari ini.</div>
    @else
    <div class="table-wrap">
      <table>
        <thead><tr><th>Daerah</th><th>Toko</th><th>Orang</th><th>Masuk</th><th>Pulang</th></tr></thead>
        <tbody>
          @foreach ($perCity as $c)
          <tr>
            <td><b>{{ $c['city'] }}</b></td>
            <td class="mono dim">{{ $c['stores'] }}</td>
            <td class="mono">{{ $c['orang'] }}</td>
            <td class="mono" style="color:#6ee7b7">{{ $c['masuk'] }}</td>
            <td class="mono" style="color:#7dd3fc">{{ $c['pulang'] }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </section>
</div>

<section class="card">
  <h2><span>Aktivitas terbaru (8 record terakhir)</span><a class="tiny" href="{{ route('admin.absensi') }}">semua record →</a></h2>
  @if ($latest->isEmpty())
    <div class="empty">Belum ada record absen sama sekali.</div>
  @else
  <div class="table-wrap">
    <table>
      <thead><tr><th>Waktu</th><th>Nama</th><th>Tipe</th><th>Toko</th><th>Daerah</th><th>Jarak</th><th>GPS (±m)</th></tr></thead>
      <tbody>
        @foreach ($latest as $a)
        <tr>
          <td class="mono nowrap">{{ $a->created_at?->format('d/m/Y H:i:s') }}</td>
          <td><b>{{ $a->name ?? $a->employee?->name ?? '—' }}</b></td>
          <td>
            @if ($a->type === 'pulang')
              <span class="pill p-sky">pulang</span>
            @else
              <span class="pill p-ok">masuk</span>
            @endif
          </td>
          <td>{{ $a->store?->name ?? '—' }}</td>
          <td class="dim">{{ $a->store?->city?->name ?? '—' }}</td>
          <td class="mono">{{ $a->distance_m !== null ? '±'.round($a->distance_m).' m' : '—' }}</td>
          <td class="mono dim">{{ $a->acc !== null ? '±'.round($a->acc) : '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</section>
@endsection
