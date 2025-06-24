@extends('layouts/contentNavbarLayout')

@section('title', 'Karyawan Tidak Aktif')

@section('content')
<div class="container">
    <h2>📜 Karyawan Tidak Aktif</h2>

    <!-- Tabel Riwayat Karyawan Tidak Aktif -->
    <table class="table mt-3">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Karyawan</th>
                <th>Tanggal Masuk</th>
                <th>Bagian</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($karyawanTidakAktif as $index => $karyawan)
            <tr>
                <td>{{ ($karyawanTidakAktif->currentPage() - 1) * $karyawanTidakAktif->perPage() + $loop->iteration }}</td>
                <td>{{ $karyawan['nama'] }}</td>
                @php
                    \Carbon\Carbon::setLocale('id');
                @endphp
                <td>{{ \Carbon\Carbon::parse($karyawan['tanggal_masuk'])->translatedFormat('d F Y') }}</td>
                <td>{{ $karyawan['role'] }}</td>
                <td>
                    <span class="badge bg-danger">Tidak Aktif</span>
                </td>
                <td>
                    <!-- Tombol untuk membuka modal detail -->
                    <button class="btn btn-outline-secondary btn-sm" onclick="showKaryawan({{ $karyawan['id'] }})">
                        <i class="fa fa-eye"></i>
                    </button>

                    <!-- Tombol untuk membuka modal edit -->
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditKaryawan" onclick="editKaryawan({{ $karyawan['id'] }})">
                        <i class="fa fa-edit"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
{{ $karyawanTidakAktif->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>

<!-- MODAL SHOW KARYAWAN -->
<div class="modal fade" id="modalShowKaryawan" tabindex="-1" aria-labelledby="modalShowKaryawanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white">
                <h3 style="color: white;" id="modalShowKaryawanLabel">Detail Karyawan</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="detailKaryawan" class="p-3">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">NIK</label>
                            <p id="show_nik" class="text-muted"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">Nama</label>
                            <p id="show_nama" class="text-muted"></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">Alamat</label>
                            <p id="show_alamat" class="text-muted"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">Tanggal Lahir</label>
                            <p id="show_tanggal_lahir" class="text-muted"></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">Jenis Kelamin</label>
                            <p id="show_jen_kel" class="text-muted"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">Bagian</label>
                            <p id="show_role" class="text-muted"></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">Status</label>
                            <p id="show_status" class="text-muted"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">Tanggal Masuk</label>
                            <p id="show_tanggal_masuk" class="text-muted"></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="fw-bold">Username</label>
                            <p id="show_username" class="text-muted"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">No Telepon</label>
                            <p id="show_no_tlp" class="text-muted"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDIT KARYAWAN -->
<div class="modal fade" id="modalEditKaryawan" tabindex="-1" aria-labelledby="modalEditKaryawanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Data Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditKaryawan" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="from" value="riwayat">
                    <input type="hidden" name="page" value="{{ request('page', 1) }}">
                    <input type="hidden" id="edit_id">

                    <div class="mb-3">
                        <label class="form-label">NIK</label>
                        <input type="text" id="edit_nik" name="nik" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" id="edit_nama" name="nama" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea id="edit_alamat" name="alamat" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" id="edit_tanggal_lahir" name="tanggal_lahir" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <div>
                            <input type="radio" id="edit_perempuan" name="jen_kel" value="P">
                            <label for="edit_perempuan">Perempuan</label>

                            <input type="radio" id="edit_laki" name="jen_kel" value="L" class="ms-3">
                            <label for="edit_laki">Laki-laki</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">No Telepon</label>
                        <input type="text" id="edit_no_tlp" name="no_tlp" class="form-control @error('no_tlp') is-invalid @enderror" value="{{ old('no_tlp', $data->no_tlp ?? '') }}" placeholder="Masukkan No Telepon" required>
                        <div class="invalid-feedback">
                            {{ $errors->first('no_tlp') ?? 'Harap masukkan No Telepon.' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Masuk</label>
                        <input type="date" id="edit_tanggal_masuk" name="tanggal_masuk" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bagian</label>
                        <select id="edit_role" name="role" class="form-control">
                            <option value="Produksi">Produksi</option>
                            <option value="Kurir">Kurir</option>
                            <option value="Toko">Toko</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="edit_status" name="status" class="form-control">
                            <option value="Aktif">Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                        </select>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function editKaryawan(id) {
        $.ajax({
            url: `/karyawan/${id}`,
            type: 'GET',
            success: function(response) {
                const data = response.data;

                $('#edit_id').val(data.id);
                $('#edit_nik').val(data.nik);
                $('#edit_nama').val(data.nama);
                $('#edit_alamat').val(data.alamat);
                $('#edit_tanggal_lahir').val(data.tanggal_lahir);
                $('#edit_role').val(data.role);
                $('#edit_no_tlp').val(data.no_tlp);
                $('#edit_tanggal_masuk').val(data.tanggal_masuk);
                $('#edit_status').val(data.status);

                if (data.jen_kel === 'P') {
                    $('#edit_perempuan').prop('checked', true);
                } else {
                    $('#edit_laki').prop('checked', true);
                }

                $('#formEditKaryawan').attr('action', `/karyawan/${id}`);
            },
            error: function(err) {
                alert('Gagal mengambil data karyawan.');
            }
        });
    }

    function showKaryawan(id) {
        $.ajax({
            url: `/karyawan/${id}`,
            type: 'GET',
            success: function(response) {
                const data = response.data;

                $('#show_nik').text(data.nik);
                $('#show_nama').text(data.nama);
                $('#show_alamat').text(data.alamat);
                $('#show_tanggal_lahir').text(data.tanggal_lahir_formatted);
                $('#show_role').text(data.role);
                $('#show_tanggal_masuk').text(data.tanggal_masuk_formatted);
                $('#show_status').text(data.status);

                if (data.jen_kel === 'P') {
                    $('#show_jen_kel').text('Perempuan');
                } else {
                    $('#show_jen_kel').text('Laki-laki');
                }

                $('#show_username').text(data.username);
                $('#show_no_tlp').text(data.no_tlp);

                $('#modalShowKaryawan').modal('show');
            },
            error: function() {
                alert('Gagal mengambil data karyawan.');
            }
        });
    }
</script>
@endsection
