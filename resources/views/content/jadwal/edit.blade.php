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
                                                    @if ($karyawan['role'] === 'Produksi')
                                                        <option value="{{ $karyawan['id'] }}"
                                                            {{ isset($jadwalHari) && in_array($karyawan['id'], explode(',', $jadwalHari['id_karyawan'] ?? '')) ? 'selected' : '' }}>
                                                            {{ $karyawan['nama'] }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        @elseif ($tempat === 'Kurir')
                                            <!-- Dropdown single khusus Kurir -->
                                            <select name="jadwal[{{ $tempat }}][{{ $shift }}][{{ $hari }}][id_karyawan]" class="form-control jadwal-select"
                                                data-hari="{{ $hari }}" data-tempat="{{ $tempat }}">
                                                <option value="">-</option>
                                                @foreach ($karyawanList as $karyawan)
                                                    @if ($karyawan['role'] === 'Kurir')
                                                        <option value="{{ $karyawan['id'] }}"
                                                            {{ isset($jadwalHari) && $jadwalHari['id_karyawan'] == $karyawan['id'] ? 'selected' : '' }}>
                                                            {{ $karyawan['nama'] }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        @else
                                            <!-- Tempat lainnya: bukan produksi dan bukan kurir -->
                                            <select name="jadwal[{{ $tempat }}][{{ $shift }}][{{ $hari }}][id_karyawan]" class="form-control jadwal-select"
                                                data-hari="{{ $hari }}" data-tempat="{{ $tempat }}">
                                                <option value="">-</option>
                                                @foreach ($karyawanList as $karyawan)
                                                    @if ($karyawan['role'] !== 'Produksi' && $karyawan['role'] !== 'Kurir')
                                                        <option value="{{ $karyawan['id'] }}"
                                                            {{ isset($jadwalHari) && $jadwalHari['id_karyawan'] == $karyawan['id'] ? 'selected' : '' }}>
                                                            {{ $karyawan['nama'] }}
                                                        </option>
                                                    @endif
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

<script>
document.querySelectorAll(".jadwal-select").forEach(function(selectEl) {
    selectEl.addEventListener("change", function () {
        const allSelects = document.querySelectorAll(".jadwal-select");
        const selectedMap = {}; // Kunci: hari + id_karyawan
        let hasDuplicate = false;

        // Reset semua warning dan border
        allSelects.forEach(sel => {
            sel.classList.remove("border-danger");
            const warningEl = sel.parentElement.querySelector(".duplicate-warning");
            if (warningEl) warningEl.remove();
        });

        allSelects.forEach(function (sel) {
            const hari = sel.dataset.hari;
            const values = Array.from(sel.selectedOptions).map(opt => opt.value).filter(val => val !== "");

            values.forEach(val => {
                const key = `${hari}-${val}`; // hanya cek duplikat di hari yang sama
                if (!selectedMap[key]) {
                    selectedMap[key] = [];
                }
                selectedMap[key].push(sel);
            });
        });

        // Tandai duplikat
        Object.keys(selectedMap).forEach(key => {
            if (selectedMap[key].length > 1) {
                selectedMap[key].forEach(el => {
                    el.classList.add("border-danger");
                    if (!el.parentElement.querySelector(".duplicate-warning")) {
                        const warning = document.createElement('div');
                        warning.classList.add('text-danger', 'duplicate-warning', 'mt-1');
                        warning.innerHTML = '⚠️ Duplikat';
                        el.parentElement.appendChild(warning);
                    }
                });
                hasDuplicate = true;
            }
        });

        // Tombol submit
        const submitButton = document.querySelector('button[type="submit"]');
        if (hasDuplicate) {
            submitButton.disabled = true;
            submitButton.classList.add("disabled");
        } else {
            submitButton.disabled = false;
            submitButton.classList.remove("disabled");
        }
    });
});
</script>
@endsection
