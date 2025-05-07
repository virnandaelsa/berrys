@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Jadwal')

@section('content')
<div class="container">
    <h2>Edit Jadwal Karyawan</h2>

    <form method="POST" action="{{ route('jadwal.update', ['id' => $jadwalData[0]['id'] ?? 0]) }}">
        @csrf
        @method('PUT')

        <input type="hidden" name="tanggal_mulai" value="{{ $tanggal_mulai->toDateString() }}">
        <input type="hidden" name="tanggal_akhir" value="{{ $tanggal_akhir->toDateString() }}">

        <div class="table-responsive">
            <table class="table mt-3">
                <thead>
                    <tr>
                        <th>Tempat</th>
                        <th>Shift</th>
                        <th>Senin</th>
                        <th>Selasa</th>
                        <th>Rabu</th>
                        <th>Kamis</th>
                        <th>Jumat</th>
                        <th>Sabtu</th>
                        <th>Minggu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jadwalList as $tempat => $shifts)
                        @php
                            $rowspan = count($shifts); // Hitung jumlah shift untuk tempat ini
                        @endphp
                        @foreach ($shifts as $index => $shift)
                            <tr>
                                <!-- Merge baris tempat -->
                                @if ($index === 0)
                                    <td rowspan="{{ $rowspan }}">{{ $tempat }}</td>
                                @endif
                                <td>{{ $shift }}</td>
                                @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                                    @php
                                        // Menemukan data jadwal berdasarkan tempat, shift, dan hari
                                        $jadwalHari = collect($jadwalData)->firstWhere(function ($item) use ($tempat, $shift, $hari) {
                                            return $item['tempat'] == $tempat && $item['shift'] == $shift && $item['hari'] == $hari;
                                        });
                                    @endphp
                                    <td>
                                        @if ($tempat === 'Produksi')
                                            <!-- Dropdown multiple untuk Produksi -->
                                            <select name="jadwal[{{ $tempat }}][{{ $shift }}][{{ $hari }}][id_karyawan][]" class="form-control" multiple>
                                                @foreach ($karyawanList as $karyawan)
                                                    <option value="{{ $karyawan['id'] }}"
                                                        {{ isset($jadwalHari) && in_array($karyawan['id'], explode(',', $jadwalHari['id_karyawan'] ?? '')) ? 'selected' : '' }}>
                                                        {{ $karyawan['nama'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @else
                                            <!-- Dropdown single untuk tempat selain Produksi -->
                                            <select name="jadwal[{{ $tempat }}][{{ $shift }}][{{ $hari }}][id_karyawan]" class="form-control">
                                                <option value="">-</option>
                                                @foreach ($karyawanList as $karyawan)
                                                    <option value="{{ $karyawan['id'] }}"
                                                        {{ isset($jadwalHari) && $jadwalHari['id_karyawan'] == $karyawan['id'] ? 'selected' : '' }}>
                                                        {{ $karyawan['nama'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
        <a href="{{ route('jadwal.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
