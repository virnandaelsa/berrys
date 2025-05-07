@extends('layouts/contentNavbarLayout')

@section('title', 'Daftar Gaji Karyawan')

@section('content')
<div class="container">
    <h2 class="text-3xl font-bold mb-6 text-gray-800">💵 Rekap Gaji Bulanan</h2>

    <!-- Form Filter Bulan dan Tahun -->
    <form method="GET" action="{{ route('penggajian.index') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="bulan" class="form-label">Bulan</label>
                <select id="bulan" name="bulan" class="form-control">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ (request('bulan', now()->format('m')) == $i) ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4">
                <label for="tahun" class="form-label">Tahun</label>
                <select id="tahun" name="tahun" class="form-control">
                    @for ($i = now()->year; $i >= 2000; $i--)
                        <option value="{{ $i }}" {{ request('tahun') == $i ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <!-- Tabel Rekap Gaji Bulanan -->
    <table class="table table-bordered">
    <thead>
        <tr>
            <th>Nama Karyawan</th>
            <th>Total Gaji</th>
            <th>Total Jam</th>
            <th>Jumlah Hari Kerja</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @if (empty($rekapGaji))
            <tr>
                <td colspan="7" class="text-center">Tidak ada data untuk bulan dan tahun yang dipilih.</td>
            </tr>
        @else
            @foreach ($rekapGaji as $rekap)
            <tr>
                <td>{{ $rekap['karyawan']['nama'] ?? 'Tidak tersedia' }}</td>
                <td style="text-align: right; white-space: nowrap;">Rp {{ number_format($rekap['total_gaji'], 0, ',', '.') }}</td>
                <td>
                    @if (isset($rekap['total_jam']))
                        @php
                            $hours = floor($rekap['total_jam']); // Ambil nilai jam
                            $minutes = ($rekap['total_jam'] - $hours) * 60; // Hitung menit
                        @endphp
                        {{ $hours }} jam {{ round($minutes) }} menit
                    @else
                        Tidak tersedia
                    @endif
                </td>
                <td>{{ $rekap['jumlah_hari_kerja'] ?? 'Tidak tersedia' }}</td>
                <td>
                    <a href="{{ route('penggajian.detail', ['id' => $rekap['id_karyawan'], 'bulan' => $rekap['bulan'], 'tahun' => $rekap['tahun']]) }}" class="btn btn-sm btn-info">
                        Detail
                    </a>
                    <a href="{{ route('penggajian.edit', ['id_karyawan' => $rekap['id_karyawan']]) }}" class="btn btn-sm btn-warning">
                        Edit Gaji
                    </a>
                </td>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>
</div>
@endsection
