@extends('layouts.contentNavbarLayout')

@section('title', 'Detail Stok Datang')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">📋 Detail Laporan Donat</h1>

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <!-- Informasi Karyawan -->
        @if ($laporanAwal)
            <div class="mb-6 bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">👩‍💼 Informasi Karyawan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <p><strong>Nama Karyawan:</strong> {{ $laporanAwal['nama'] ?? 'Unknown' }}</p>
                    <p><strong>Shift:</strong> {{ $laporanAwal['shift'] ?? 'Unknown' }}</p>
                    <p><strong>Tempat:</strong> {{ $laporanAwal['tempat'] ?? 'Unknown' }}</p>
                    @php
                        \Carbon\Carbon::setLocale('id');
                        $tanggalFormatted = \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y');
                    @endphp
                    <p><strong>Tanggal:</strong> {{ $tanggalFormatted }}</p>
                </div>
            </div>
        @else
            <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg">
                Data laporan awal tidak ditemukan.
            </div>
        @endif

        <div class="mb-6">
            <h2>📦 Detail Laporan Datang</h2>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Donat Bombo</th>
                        <th>Donat Bolong</th>
                        <th>Donat Salju</th>
                        <th>Catatan</th>
                        <th>Kelengkapan</th>
                    </tr>
                </thead>
                <tbody>
                    @if (empty($laporanAwal))
                        <tr>
                            <td colspan="5" class="text-center text-gray-500 py-4">
                                Tidak ada detail laporan datang.
                            </td>
                        </tr>
                    @else
                        <tr class="hover:bg-gray-100">
                            <td>{{ $laporanAwal['donat_bombo'] ?? 0 }}</td>
                            <td>{{ $laporanAwal['donat_bolong'] ?? 0 }}</td>
                            <td>{{ $laporanAwal['donat_salju'] ?? 0 }}</td>
                            <td>{!! nl2br(e($laporanAwal['catatan'] ?? '-')) !!}</td>
                            <td>
                                <ul>
                                    @foreach(explode("\n", $laporanAwal['kelengkapan'] ?? '') as $item)
                                        @if(trim($item) !== '')
                                            <li>{{ $item }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Tabel Detail Stok Datang -->
        <div class="mb-6">
            <h2>📦 Detail Stok Datang</h2>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Jam</th>
                            <th>Donat Bombo</th>
                            <th>Donat Bolong</th>
                            <th>Donat Salju</th>
                            <th>Catatan</th>
                            <th>Kelengkapan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stokDetails as $detail)
                            <tr>
                                <td>{{ $detail['jam'] ?? '-' }}</td>
                                <td>{{ $detail['donat_bombo'] ?? 0 }}</td>
                                <td>{{ $detail['donat_bolong'] ?? 0 }}</td>
                                <td>{{ $detail['donat_salju'] ?? 0 }}</td>
                                <td>{!! nl2br(e($detail['catatan'] ?? '-')) !!}</td>
                                <td>
                                    <ul>
                                        @foreach(explode("\n", $detail['kelengkapan'] ?? '') as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-gray-500 py-4">Tidak ada detail stok datang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        <!-- Tabel Laporan Pulang -->
        <div class="mb-6">
            <h2>📦 Laporan Pulang</h2>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Stok Bombo</th>
                        <th>Stok Bolong</th>
                        <th>Stok Salju</th>
                        <th>Catatan</th>
                        <th>Kelengkapan</th>
                    </tr>
                </thead>
                <tbody>
                    @if (empty($laporanPulang))
                        <tr>
                            <td colspan="5" class="text-center text-gray-500 py-4">
                                Data laporan pulang tidak ditemukan.
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td>{{ $laporanPulang['stok_bombo'] ?? 0 }}</td>
                            <td>{{ $laporanPulang['stok_bolong'] ?? 0 }}</td>
                            <td>{{ $laporanPulang['stok_salju'] ?? 0 }}</td>
                            <td>{!! nl2br(e($laporanPulang['catatan'] ?? '-')) !!}</td>
                            <td>
                                <ul>
                                    @foreach(explode("\n", $laporanPulang['kelengkapan'] ?? '') as $item)
                                        @if(trim($item) !== '')
                                            <li>{{ $item }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <!-- Tombol Kembali -->
        <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('laporan.donat') }}" class="btn btn-primary me-2">Kembali</a>
        </div>
    </div>
@endsection
