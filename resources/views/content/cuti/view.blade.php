@extends('layouts/contentNavbarLayout')

@section('title', 'Cuti Karyawan')

@section('content')
<div class="container">
    <h2 class="text-3xl font-bold mb-6 text-gray-800">Cuti Karyawan</h2>
    @php
        // Jika user tidak memilih tanggal, atur default ke minggu ini
        $tanggalMulai = request('tanggal_mulai')
            ? \Carbon\Carbon::parse(request('tanggal_mulai'))->startOfWeek()->toDateString()
            : now()->startOfWeek()->toDateString();

        $tanggalAkhir = request('tanggal_akhir')
            ? \Carbon\Carbon::parse(request('tanggal_akhir'))->endOfWeek()->toDateString()
            : now()->endOfWeek()->toDateString();
    @endphp

    <form method="GET" action="{{ route('cuti.index') }}">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex">
                <input type="date" id="tanggal_mulai" name="tanggal_mulai"
                    value="{{ old('tanggal_mulai', $tanggal_mulai->format('Y-m-d')) }}"
                    class="form-control mr-2" style="width: 200px;">
                <span class="mx-2">-</span>
                <input type="date" id="tanggal_akhir" name="tanggal_akhir"
                    value="{{ old('tanggal_akhir', $tanggal_akhir->format('Y-m-d')) }}"
                    class="form-control" style="width: 200px;" readonly>
                <span class="mx-2"></span>
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <div class="mt-3">
        @foreach ($cutiData as $cuti)
        <div class="cuti-card d-flex align-items-center p-3 border rounded mb-2">
            <input type="checkbox" class="mr-2 checkbox-cuti" data-id="{{ $cuti['id'] }}"
                @if($cuti['status'] === 'Diterima') checked @endif>
            <div class="flex-grow-1">
                <p class="nama-karyawan mb-1">
                    {{ $cuti['nama_karyawan'] }}
                    <span class="tanggal">({{ \Carbon\Carbon::parse($cuti['tanggal'])->format('d/m/Y') }})</span>
                </p>
                <p class="alasan-cuti text-muted mb-0">{{ $cuti['alasan'] }}</p>
            </div>
            <div>
                <!-- Form submit untuk update status -->
                <form action="{{ route('cuti.update', $cuti['id']) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="Ditolak">
                    <button type="submit" class="btn btn-danger btn-sm">Tolak</button>
                </form>
                <form action="{{ route('cuti.update', $cuti['id']) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="Diterima">
                    <button type="submit" class="btn btn-success btn-sm">Terima</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

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

    document.querySelectorAll('.checkbox-cuti').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            let cutiId = this.getAttribute('data-id');
            let checked = this.checked;

            console.log(`Cuti ID ${cutiId} telah ${checked ? 'ditandai' : 'tidak ditandai'}`);
        });
    });
</script>
@endsection
