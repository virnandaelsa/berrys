@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Gaji')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h2 class="text-3xl font-bold mb-6 text-gray-800">Edit Gaji Bonus/Potongan</h2>
        </div>
        <div class="card-body">
            <h4 class="mb-3"><strong>Nama:</strong> {{ $karyawan['nama'] ?? 'Tidak tersedia' }}</h4>
            <p><strong>Bagian:</strong> {{ $karyawan['role'] ?? 'Tidak tersedia' }}</p>
            <p><strong>Bulan:</strong> {{ \Carbon\Carbon::create()->month((int) $bulan)->translatedFormat('F') }}</p>
            <p><strong>Tahun:</strong> {{ $tahun }}</p>

            <form method="POST" action="{{ route('penggajian.tambahEditGaji') }}" class="needs-validation" novalidate>
                @csrf
                <input type="hidden" name="id_karyawan" value="{{ $karyawan['id'] }}">
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <input type="hidden" name="tahun" value="{{ $tahun }}">

                <div class="mb-4">
                    <label for="jenis" class="form-label"><strong>Jenis</strong></label>
                    <select name="jenis" id="jenis" class="form-select" required>
                        <option value="" disabled selected>Pilih Jenis</option>
                        <option value="bonus" {{ old('jenis') == 'Bonus' ? 'selected' : '' }}>Bonus</option>
                        <option value="potongan" {{ old('jenis') == 'Potongan' ? 'selected' : '' }}>Potongan</option>
                    </select>
                    <div class="invalid-feedback">
                        Jenis harus dipilih.
                    </div>
                </div>

                <div class="mb-4">
                    <label for="jumlah" class="form-label"><strong>Jumlah Uang</strong></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="jumlah" id="jumlah" class="form-control" value="{{ old('jumlah') }}" required>
                    </div>
                    <div class="invalid-feedback">
                        Jumlah uang harus diisi.
                    </div>
                </div>

                <div class="mb-4">
                    <label for="keterangan" class="form-label"><strong>Keterangan</strong></label>
                    <textarea name="keterangan" id="keterangan" class="form-control" rows="3" placeholder="Masukkan keterangan..." required>{{ old('keterangan') }}</textarea>
                    <div class="invalid-feedback">
                        Keterangan harus diisi.
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-2">Simpan</button>
                    <a href="{{ route('penggajian.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Bootstrap form validation
    (function () {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
</script>
@endsection
