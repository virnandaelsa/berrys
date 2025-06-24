@extends('layouts/contentNavbarLayout')

@section('title', 'Lihat Jadwal')

@section('content')
<div class="container">
    <h2>📅 Jadwal Karyawan</h2>
    <div class="d-flex justify-content-between">
        <div>
            <a href="{{ route('jadwal.create') }}"
            class="btn btn-primary">+ Tambah</a>

            <a href="{{ route('jadwal.edit', ['tanggal_mulai' => $tanggal_mulai->toDateString()]) }}"
            class="btn btn-secondary {{ count($groupedJadwal) ? '' : 'disabled' }}">✎ Edit</a>
        </div>
        <div>
            <a href="{{ route('cuti.index') }}" class="btn btn-warning">Cuti Karyawan</a>
        </div>
    </div>
    <br>

    @php
        // Jika user tidak memilih tanggal, atur default ke minggu ini
        $tanggalMulai = request('tanggal_mulai')
            ? \Carbon\Carbon::parse(request('tanggal_mulai'))->startOfWeek()->toDateString()
            : now()->startOfWeek()->toDateString();

        $tanggalAkhir = request('tanggal_akhir')
            ? \Carbon\Carbon::parse(request('tanggal_akhir'))->endOfWeek()->toDateString()
            : now()->endOfWeek()->toDateString();
    @endphp

    <form method="GET" action="{{ route('jadwal.index') }}">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex">
                <input type="date" id="tanggal_mulai" name="tanggal_mulai"
                    value="{{ old('tanggal_mulai', $tanggalMulai) }}"
                    class="form-control mr-2" style="width: 200px;">
                <span class="mx-2">-</span>
                <input type="date" id="tanggal_akhir" name="tanggal_akhir"
                    value="{{ old('tanggal_akhir', $tanggalAkhir) }}"
                    class="form-control" style="width: 200px;" readonly>
                <span class="mx-2"></span>
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <table class="table mt-3 table-bordered text-center">
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
            @foreach ($shifts as $shift)
                @php
                    // Cari data yang cocok dari groupedJadwal
                    $data = collect($groupedJadwal)->first(function($item) use ($tempat, $shift) {
                        return $item['tempat'] == $tempat && $item['shift'] == $shift;
                    });
                @endphp
                <tr>
                    <td>{{ $tempat }}</td>
                    <td>{{ $shift }}</td>
                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                        <td>{{ $data['hari'][$hari] ?? '-' }}</td>
                    @endforeach
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
</div>

<style>
    /* Pastikan ukuran kolom hari sama */
    table th, table td {
        width: 100px; /* Tentukan ukuran kolom */
        vertical-align: middle; /* Pusatkan teks secara vertikal */
    }

    /* Tambahkan garis pembatas antar kolom */
    .table-bordered th, .table-bordered td {
        border: 1px solid #dee2e6;
    }

    /* Pusatkan teks di setiap kolom */
    .table th, .table td {
        text-align: center;
    }
</style>

<script>
    document.getElementById("tanggal_mulai").addEventListener("change", function () {
        let tanggalMulai = new Date(this.value);

        // Pastikan tanggal mulai selalu Senin
        let dayOfWeek = tanggalMulai.getDay(); // 0 = Minggu, 1 = Senin, ..., 6 = Sabtu
        if (dayOfWeek !== 1) { // Jika bukan Senin
            let daysToMonday = (dayOfWeek === 0) ? -6 : (1 - dayOfWeek); // Jika Minggu, geser ke Senin sebelumnya
            tanggalMulai.setDate(tanggalMulai.getDate() + daysToMonday);
        }

        // Tanggal akhir adalah Minggu dalam minggu yang sama
        let tanggalAkhir = new Date(tanggalMulai);
        tanggalAkhir.setDate(tanggalMulai.getDate() + 6); // Tambah 6 hari

        // Format tanggal ke YYYY-MM-DD
        let formatTanggal = (date) => {
            let d = date.getDate().toString().padStart(2, '0');
            let m = (date.getMonth() + 1).toString().padStart(2, '0');
            let y = date.getFullYear();
            return `${y}-${m}-${d}`;
        };

        // Set tanggal mulai dan akhir
        document.getElementById("tanggal_mulai").value = formatTanggal(tanggalMulai);
        document.getElementById("tanggal_akhir").value = formatTanggal(tanggalAkhir);
    });
</script>

@endsection
