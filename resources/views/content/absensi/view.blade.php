@extends('layouts/contentNavbarLayout')

@section('title', 'Daftar Absensi Karyawan')

@section('content')

<div class="container">
    <h2 class="text-3xl font-bold mb-6 text-gray-800">📋 Daftar Absensi Karyawan</h2>

    <!-- Filter Tanggal -->
    <form method="GET" action="{{ route('absensi.index') }}" class="d-flex mb-3">
        <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control mr-2" style="width: 200px;">
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    @if(session('message'))
    <div class="alert alert-warning">
        {{ session('message') }}
    </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Karyawan</th>
                <th>Tempat</th>
                <th>Shift</th>
                <th>Jam Datang</th>
                <th>Jam Pulang</th>
                <th>Foto Datang</th>
                <th>Foto Pulang</th>
            </tr>
        </thead>
        <tbody>
            @forelse($karyawanData as $key => $karyawan)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $karyawan['karyawan']['nama'] ?? 'Tidak tersedia' }}</td>
                    <td>
                        {{ $karyawan['absensi'][0]['jadwal']['tempat'] ?? 'Tidak tersedia' }}
                    </td>
                    <td>
                        {{ $karyawan['absensi'][0]['jadwal']['shift'] ?? 'Tidak tersedia' }}
                    </td>

                    {{-- Absensi Pertama --}}
                    <td>
                        @if(isset($karyawan['absensi'][0]))
                            {{ \Carbon\Carbon::parse($karyawan['absensi'][0]['jam'])->format('H:i') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        {{-- Absensi Kedua --}}
                        @if(isset($karyawan['absensi'][1]))
                            {{ \Carbon\Carbon::parse($karyawan['absensi'][1]['jam'])->format('H:i') }}
                        @else
                            -
                        @endif
                    </td>

                    <td>
                        @if(isset($karyawan['absensi'][0]['photo']) && $karyawan['absensi'][0]['photo'])
                            <img src="{{ config('api.photo_url') . '/' . $karyawan['absensi'][0]['photo'] }}" alt="Foto Absensi 1" width="100" height="100">
                        @else
                            <span>No Photo</span>
                        @endif
                    </td>
                    <td>
                        @if(isset($karyawan['absensi'][1]['photo']) && $karyawan['absensi'][1]['photo'])
                            <img src="{{ config('api.photo_url') . '/' . $karyawan['absensi'][1]['photo'] }}" alt="Foto Absensi 2" width="100" height="100">
                        @else
                            <span>No Photo</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data absensi untuk tanggal {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
