@extends('layouts.contentNavbarLayout')

@section('title', 'Pendapatan Karyawan')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">📊 Laporan Pendapatan Karyawan</h1>

    <!-- Filter Tanggal -->
    <form method="GET" action="{{ route('pendapatan') }}">
        <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex">
            <div>
                <input type="date" name="tanggal" id="tanggal" value="{{ $tanggal }}" class="form-control mr-2" style="width: 200px;">
            </div>
            <span class="mx-2"></span>
            <div>
                <button type="submit" class="btn btn-primary">
                    Filter
                </button>
            </div>
        </div>
        </div>
    </form>

     <!-- Tabel Pendapatan -->
     <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">💼 Rincian Pendapatan</h2>
            @if (!empty($pendapatan['data']) && count($pendapatan['data']) > 0)
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th style="text-align: center;">Nama Karyawan</th>
                            <th style="text-align: center;">Tempat</th>
                            <th style="text-align: center;">Shift</th>
                            <th style="width: 120px; text-align: center; white-space: nowrap;">Omset</th>
                            <th style="width: 120px; text-align: center; white-space: nowrap;">Fisik</th>
                            <th style="width: 120px; text-align: center; white-space: nowrap;">Pengeluaran</th>
                            <th style="width: 120px; text-align: center; white-space: nowrap;">Omset Sistem</th>
                            <th style="text-align: center;">Status</th>
                            <th style="width: 120px; text-align: center; white-space: nowrap;">Selisih</th>
                            <th style="text-align: center;">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendapatan['data'] as $item)
                            @php
                                $balance = ($item['omset'] ?? 0) === ($item['omset_sistem'] ?? 0);
                                $selisih = ($item['omset'] ?? 0) - ($item['omset_sistem'] ?? 0);
                            @endphp
                            <tr class="hover:bg-gray-100">
                                <td>{{ $item['nama_karyawan'] ?? '-' }}</td>
                                <td>{{ $item['tempat'] ?? '-' }}</td>
                                <td>{{ $item['shift'] ?? '-' }}</td>
                                <td style="text-align: right; white-space: nowrap;">Rp {{ number_format($item['omset'] ?? 0, 0, ',', '.') }}</td>
                                <td style="text-align: right; white-space: nowrap;">Rp {{ number_format($item['fisik'] ?? 0, 0, ',', '.') }}</td>
                                <td style="text-align: right; white-space: nowrap;">Rp {{ number_format($item['pengeluaran'] ?? 0, 0, ',', '.') }}</td>
                                <td style="text-align: right; white-space: nowrap;">Rp {{ number_format($item['omset_sistem'] ?? 0, 0, ',', '.') }}</td>
                                <td style="color: {{ $balance ? '#28a745' : '#dc3545' }};">
                                    {{ $balance ? 'Balance' : 'Not Balance' }}
                                </td>
                                <td style="text-align: right; white-space: nowrap; color: {{ $selisih === 0 ? '#6c757d' : '#dc3545' }};">
                                    Rp {{ number_format($selisih, 0, ',', '.') }}
                                </td>
                                <td>{{ $item['catatan'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center text-gray-500 py-4">
                    Tidak ada pendapatan di tanggal ini.
                </div>
            @endif
        </div>
    </div>
@endsection
