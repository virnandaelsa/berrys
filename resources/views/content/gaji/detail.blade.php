@extends('layouts/contentNavbarLayout')

@section('title', 'Detail Gaji Karyawan')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h2 style="color: white;">Detail Gaji Karyawan</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="mb-3"><strong>Informasi Karyawan</strong></h4>
                        <p class="mb-3"><strong>Nama:</strong> {{ $karyawan['nama'] ?? 'Tidak tersedia' }}</p>
                        <p class="mb-3"><strong>NIK:</strong> {{ $karyawan['nik'] ?? 'Tidak tersedia' }}</p>
                        <p class="mb-3"><strong>Alamat:</strong> {{ $karyawan['alamat'] ?? 'Tidak tersedia' }}</p>
                        <p class="mb-3"><strong>Tanggal Lahir:</strong> {{ $karyawan['tanggal_lahir'] ? \Carbon\Carbon::parse($karyawan['tanggal_lahir'])->translatedFormat('d F Y') : 'Tidak tersedia' }}</p>
                        <p class="mb-3"><strong>Jenis Kelamin:</strong> {{ $karyawan['jen_kel'] ?? 'Tidak tersedia' }}</p>
                </div>
                <div class="col-md-6">
                    <h4 class="mb-3"><strong>Detail Pekerjaan</strong></h4>
                    <p class="mb-3"><strong>Bagian:</strong> {{ $karyawan['role'] ?? 'Tidak tersedia' }}</p>
                    <p class="mb-3"><strong>Status:</strong> {{ $karyawan['status'] ?? 'Tidak tersedia' }}</p>
                    <p class="mb-3"><strong>Tanggal Masuk:</strong> {{ $karyawan['tanggal_masuk'] ? \Carbon\Carbon::parse($karyawan['tanggal_masuk'])->translatedFormat('d F Y') : 'Tidak tersedia' }}</p>
                </div>
            </div>

            <hr class="my-4">

            <h4 class="mb-3"><strong>Periode Penggajian</strong></h4>
            <p class="mb-3"><strong>Bulan:</strong> {{ \Carbon\Carbon::create()->month((int) $rekap['bulan'])->translatedFormat('F') }}</p>
            <p class="mb-3"><strong>Tahun:</strong> {{ $rekap['tahun'] }}</p>

            <hr class="my-4">

            <h4 class="mb-3"><strong>Detail Gaji</strong></h4>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Total Gaji</strong></td>
                        <td style="text-align: right; white-space: nowrap;">Rp {{ number_format($rekap['total_gaji'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Jumlah Hari Kerja</strong></td>
                        <td>{{ $rekap['jumlah_hari_kerja'] ?? 'Tidak tersedia' }} hari</td>
                    </tr>
                    <tr>
                        <td><strong>Total Jam Kerja</strong></td>
                        <td>
                            @if(isset($rekap['total_jam']))
                                @php
                                    $hours = floor($rekap['total_jam']);
                                    $minutes = ($rekap['total_jam'] - $hours) * 60;
                                @endphp
                                {{ $hours }} jam {{ round($minutes) }} menit
                            @else
                                Tidak tersedia
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Total Bonus</strong></td>
                        <td style="text-align: right; white-space: nowrap;">Rp {{ number_format($rekap['total_bonus'], 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Potongan</strong></td>
                        <td style="text-align: right; white-space: nowrap;">Rp {{ number_format(abs($rekap['total_potongan']), 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            <hr class="my-4">

            <h4 class="mb-3"><strong>Detail Bonus dan Potongan</strong></h4>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekap['edit_gaji_details'] as $edit)
                        <tr>
                            <td>{{ ucfirst($edit['jenis']) }}</td>
                            <td style="text-align: right; white-space: nowrap;">Rp {{ number_format(abs($edit['jumlah']), 0, ',', '.') }}</td>
                            <td>{{ $edit['keterangan'] ?? 'Tidak ada keterangan' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Tidak ada bonus atau potongan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('penggajian.index') }}" class="btn btn-primary me-2">Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection
