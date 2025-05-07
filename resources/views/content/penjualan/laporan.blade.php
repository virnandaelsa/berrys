@extends('layouts/contentNavbarLayout')

@section('title', 'Laporan Stok')

@section('content')
<div class="container">
    <div class="container mx-auto p-6">
        <h2 class="text-3xl font-bold mb-6 text-gray-800">📊 Laporan Donat</h2>

        <!-- Filter Tanggal -->
        <form method="GET" action="{{ route('laporan.donat') }}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <!-- Input Tanggal -->
                <div class="d-flex">
                <div>
                    <input
                        type="date"
                        id="tanggal"
                        name="tanggal"
                        value="{{ $tanggal }}"
                        class="form-control mr-2" style="width: 200px;"
                    />
                </div>
                <span class="mx-2"></span>
                <!-- Tombol Filter -->
                <div>
                    <button
                        type="submit"
                        class="btn btn-primary">
                        Filter
                    </button>
                </div>
                </div>
            </div>
        </form>

        <!-- Tabel -->
            <table class="table mt-3">
                <thead>
                    <tr>
                        <th>Nama Karyawan</th>
                        <th>Shift</th>
                        <th>Tempat</th>
                        <th>Laporan Datang</th>
                        <th>Stok Datang (Total)</th>
                        <th>Laporan Pulang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($karyawanData as $item)
                        <tr>
                            <td>{{ $item['nama_karyawan'] }}</td>
                            <td>{{ $item['shift'] }}</td>
                            <td>{{ $item['tempat'] }}</td>
                            <td>
                                <div class="text-sm">
                                    <strong>Bombo:</strong> {{ $item['laporan_datang']['bombo'] }}<br>
                                    <strong>Bolong:</strong> {{ $item['laporan_datang']['bolong'] }}<br>
                                    <strong>Salju:</strong> {{ $item['laporan_datang']['salju'] }}
                                </div>
                            </td>
                            <td class="px-6 py-4 border">
                                <div class="text-sm">
                                    <strong>Bombo:</strong> {{ $item['stok_datang_total']['bombo'] }}<br>
                                    <strong>Bolong:</strong> {{ $item['stok_datang_total']['bolong'] }}<br>
                                    <strong>Salju:</strong> {{ $item['stok_datang_total']['salju'] }}
                                </div>
                            </td>
                            <td class="px-6 py-4 border">
                                <div class="text-sm">
                                    <strong>Bombo:</strong> {{ $item['laporan_pulang']['bombo'] }}<br>
                                    <strong>Bolong:</strong> {{ $item['laporan_pulang']['bolong'] }}<br>
                                    <strong>Salju:</strong> {{ $item['laporan_pulang']['salju'] }}
                                </div>
                            </td>
                            <td class="px-6 py-4 border">
                                <a
                                    href="{{ route('laporan.donat.detail', ['id_jadwal' => $item['id_jadwal'], 'tanggal' => $tanggal]) }}"
                                    class="btn btn-primary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-gray-500 py-6">
                                Tidak ada data untuk tanggal ini. 🕵️‍♂️
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
</div>
@endsection
