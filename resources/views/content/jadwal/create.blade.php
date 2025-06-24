@extends('layouts/contentNavbarLayout')

@section('title', 'Tambah Jadwal Karyawan')

@section('content')
<div class="container">
    <h2>Tambah Jadwal Karyawan</h2>

    <!-- Form untuk input jadwal -->
    <form action="{{ route('jadwal.store') }}" method="POST">
        @csrf
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex">
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $tanggal_mulai->format('Y-m-d')) }}" class="form-control mr-2" style="width: 200px;">
                <span class="mx-2">-</span>
                <input type="date" id="tanggal_akhir" name="tanggal_akhir" value="{{ old('tanggal_akhir', $tanggal_akhir->format('Y-m-d')) }}" class="form-control" style="width: 200px;" readonly>
            </div>
        </div>

        <!-- Tabel Jadwal -->
        <div class="table-responsive">
            <table class="table mt-3">
                <thead>
                    <tr>
                        <th>Tempat</th>
                        <th>Shift</th>
                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                            <th>{{ $hari }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jadwalList as $tempat => $shifts)
                        @php
                            $rowspan = count($shifts); // Hitung jumlah shift untuk tempat ini
                        @endphp
                        @foreach ($shifts as $index => $shift)
                            <tr>
                                @if ($index === 0)
                                    <td rowspan="{{ $rowspan }}">{{ $tempat }}</td>
                                @endif
                                <td>{{ $shift }}</td>
                                @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                                    @php
                                        $tanggal_hari_ini = $tanggalPerHari[$hari];
                                    @endphp
                                    <td>
                                        <!-- Hidden input untuk mengirim data tempat, shift, dan hari -->
                                        <input type="hidden" name="jadwal[{{ $tempat }}][{{ $shift }}][{{ $hari }}][tempat]" value="{{ $tempat }}">
                                        <input type="hidden" name="jadwal[{{ $tempat }}][{{ $shift }}][{{ $hari }}][shift]" value="{{ $shift }}">
                                        <input type="hidden" name="jadwal[{{ $tempat }}][{{ $shift }}][{{ $hari }}][hari]" value="{{ $hari }}">

                                        @if ($tempat === 'Produksi')
                                            <!-- Dropdown multiple untuk Produksi, hanya tampilkan karyawan role 'produksi' -->
                                            <select name="jadwal[{{ $tempat }}][{{ $shift }}][{{ $hari }}][id_karyawan][]" class="form-control" multiple>
                                                <option value="" selected>-</option>
                                                @foreach ($karyawanList as $karyawan)
                                                    @if ($karyawan['role'] === 'Produksi')
                                                        @php
                                                            $cutiTanggal = $cutiByKaryawan[$karyawan['id']] ?? [];
                                                            $sedangCuti = in_array($tanggal_hari_ini, $cutiTanggal);
                                                        @endphp
                                                        @if (!$sedangCuti)
                                                            <option value="{{ $karyawan['id'] }}">{{ $karyawan['nama'] }}</option>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </select>
                                        @elseif ($tempat === 'Kurir')
                                            <!-- Dropdown single untuk Kurir, hanya tampilkan karyawan role 'kurir' -->
                                            <select name="jadwal[{{ $tempat }}][{{ $shift }}][{{ $hari }}][id_karyawan]" class="form-control jadwal-select"
                                                data-hari="{{ $hari }}" data-tempat="{{ $tempat }}">
                                                <option value="" selected>-</option>
                                                @foreach ($karyawanList as $karyawan)
                                                    @if ($karyawan['role'] === 'Kurir')
                                                        @php
                                                            $cutiTanggal = $cutiByKaryawan[$karyawan['id']] ?? [];
                                                            $sedangCuti = in_array($tanggal_hari_ini, $cutiTanggal);
                                                        @endphp
                                                        @if (!$sedangCuti)
                                                            <option value="{{ $karyawan['id'] }}">{{ $karyawan['nama'] }}</option>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </select>
                                        @else
                                            <!-- Tempat lainnya, tampilkan semua selain produksi & kurir -->
                                            <select name="jadwal[{{ $tempat }}][{{ $shift }}][{{ $hari }}][id_karyawan]" class="form-control jadwal-select"
                                                data-hari="{{ $hari }}" data-tempat="{{ $tempat }}">
                                                <option value="" selected>-</option>
                                                @foreach ($karyawanList as $karyawan)
                                                    @if ($karyawan['role'] !== 'Produksi' && $karyawan['role'] !== 'Kurir')
                                                        @php
                                                            $cutiTanggal = $cutiByKaryawan[$karyawan['id']] ?? [];
                                                            $sedangCuti = in_array($tanggal_hari_ini, $cutiTanggal);
                                                        @endphp
                                                        @if (!$sedangCuti)
                                                            <option value="{{ $karyawan['id'] }}">{{ $karyawan['nama'] }}</option>
                                                        @endif
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

        <!-- Tombol Simpan -->
        <div class="text-center mt-3">
            <button type="submit" class="btn btn-primary">SIMPAN</button>
        </div>
    </form>
</div>

<script>
document.getElementById("tanggal_mulai").addEventListener("change", function () {
    let tglMulai = new Date(this.value);

    // Pastikan tanggal yang dipilih adalah Senin (1 = Senin)
    if (tglMulai.getDay() !== 1) {
        alert("Harap pilih tanggal mulai yang merupakan hari Senin.");
        this.value = ""; // Reset input
        document.getElementById("tanggal_akhir").value = ""; // Reset tanggal akhir juga
        return;
    }

    // Tanggal akhir otomatis menjadi Minggu (6 hari setelah Senin)
    let tglAkhir = new Date(tglMulai);
    tglAkhir.setDate(tglAkhir.getDate() + 6);
    document.getElementById("tanggal_akhir").value = tglAkhir.toISOString().split("T")[0];
});
</script>

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
