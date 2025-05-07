@extends('layouts/contentNavbarLayout')

@section('title', 'Data Karyawan')

@section('content')
<div class="container">
    <h2>👩‍💻 Data Karyawan</h2>

    <!-- Tombol Tambah Karyawan -->
    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalTambahKaryawan">
        Tambah Karyawan
    </button>

    <!-- Tabel Data Karyawan -->
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
            @foreach($karyawanAktif as $index => $karyawan)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $karyawan['nama'] }}</td>
                <td>{{ \Carbon\Carbon::parse($karyawan['tanggal_masuk'])->format('d/m/Y') }}</td>
                <td>{{ $karyawan['role'] }}</td>
                <td>
                    <span class="badge bg-success">Aktif</span>
                </td>
                <td>
                    <button class="btn btn-outline-secondary btn-sm" onclick="showKaryawan({{ $karyawan['id'] }})">
                        <i class="fa fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalEditKaryawan" onclick="editKaryawan({{ $karyawan['id'] }})">
                        <i class="fa fa-edit"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- MODAL TAMBAH KARYAWAN -->
<div class="modal fade" id="modalTambahKaryawan" tabindex="-1" aria-labelledby="modalTambahKaryawanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTambahKaryawan" action="{{ route('karyawan.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">NIK</label>
                        <input type="text" name="nik" class="form-control" placeholder="Masukkan NIK" required>
                        <div class="invalid-feedback">Harap masukkan NIK.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan Nama" required>
                        <div class="invalid-feedback">Harap masukkan Nama Karyawan.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3" placeholder="Masukkan Alamat" required></textarea>
                        <div class="invalid-feedback">Harap masukkan Alamat.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control" required>
                        <div class="invalid-feedback">Harap pilih Tanggal Lahir.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <div>
                            <input type="radio" name="jen_kel" value="P" id="perempuan" required>
                            <label for="perempuan">Perempuan</label>

                            <input type="radio" name="jen_kel" value="L" id="laki" class="ms-3" required>
                            <label for="laki">Laki-laki</label>
                        </div>
                        <div class="invalid-feedback">Harap pilih Jenis Kelamin.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Masuk</label>
                        <input type="date" name="tanggal_masuk" class="form-control" required>
                        <div class="invalid-feedback">Harap pilih Tanggal Masuk.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bagian</label>
                        <select name="role" class="form-control" required>
                            <option value="">Pilih Bagian</option>
                            <option value="Produksi">Produksi</option>
                            <option value="Kurir">Kurir</option>
                            <option value="Toko">Toko</option>
                        </select>
                        <div class="invalid-feedback">Harap pilih Bagian.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan Username" required>
                        <div class="invalid-feedback">Harap masukkan Username.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required>
                        <div class="invalid-feedback">Harap masukkan Password.</div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary px-4">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
                            <input type="radio" id="edit_perempuan" name="jen_kel" value="P"
                                @if(old('jen_kel', $data->jen_kel ?? '') == 'P') checked @endif>
                            <label for="edit_perempuan">Perempuan</label>

                            <input type="radio" id="edit_laki" name="jen_kel" value="L" class="ms-3"
                                @if(old('jen_kel', $data->jen_kel ?? '') == 'L') checked @endif>
                            <label for="edit_laki">Laki-laki</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Masuk</label>
                        <input type="date" id="edit_tanggal_masuk" name="tanggal_masuk" class="form-control"
                            value="{{ old('tanggal_masuk', $data->tanggal_masuk ?? '') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bagian</label>
                        <select id="edit_role" name="role" class="form-control">
                            <option value="Produksi" @selected(old('role', $data->role ?? '') == 'Produksi')>Produksi</option>
                            <option value="Kurir" @selected(old('role', $data->role ?? '') == 'Kurir')>Kurir</option>
                            <option value="Toko" @selected(old('role', $data->role ?? '') == 'Toko')>Toko</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="edit_status" name="status" class="form-control">
                            <option value="Aktif" @selected(old('status', $data->status ?? '') == 'Aktif')>Aktif</option>
                            <option value="Tidak Aktif" @selected(old('status', $data->status ?? '') == 'Tidak Aktif')>Tidak Aktif</option>
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

<!-- MODAL HAPUS KARYAWAN -->
<div class="modal fade" id="modalDeleteKaryawan" tabindex="-1" aria-labelledby="modalDeleteKaryawanLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Penghapusan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data karyawan ini?</p>
            </div>
            <div class="modal-footer">
                <form id="formDeleteKaryawan" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
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
                // Log respons untuk memverifikasi format data
                console.log("Respons API:", response);

                // Ambil data dari response.data
                const data = response.data;

                // Cek apakah data undefined
                if (!data) {
                    console.error("Data tidak ditemukan dalam respons:", response);
                    alert("Data tidak ditemukan.");
                    return;
                }

                // Isi form dengan data karyawan
                $('#edit_id').val(data.id);
                $('#edit_nik').val(data.nik);
                $('#edit_nama').val(data.nama);
                $('#edit_alamat').val(data.alamat);
                $('#edit_tanggal_lahir').val(data.tanggal_lahir);
                $('#edit_role').val(data.role);
                $('#edit_tanggal_masuk').val(data.tanggal_masuk);
                $('#edit_status').val(data.status);

                // Pilih radio button berdasarkan jenis kelamin
                if (data.jen_kel === 'P') {
                    $('#edit_perempuan').prop('checked', true);
                } else {
                    $('#edit_laki').prop('checked', true);
                }

                // Ubah action form untuk update
                $('#formEditKaryawan').attr('action', `/karyawan/${id}`);
            },
            error: function(err) {
                console.error("Gagal mengambil data karyawan", err);
                alert("Gagal mengambil data karyawan.");
            }
        });
    }

    function showKaryawan(id) {
        $.ajax({
            url: `/karyawan/${id}`,
            type: 'GET',
            success: function(response) {
                // Ambil data dari response.data
                const data = response.data;

                // Isi elemen modal dengan data karyawan
                $('#show_nik').text(data.nik);
                $('#show_nama').text(data.nama);
                $('#show_alamat').text(data.alamat);
                $('#show_tanggal_lahir').text(data.tanggal_lahir);
                $('#show_role').text(data.role);
                $('#show_tanggal_masuk').text(data.tanggal_masuk);
                $('#show_status').text(data.status);

                if (data.jen_kel === 'P') {
                    $('#show_jen_kel').text('Perempuan');
                } else {
                    $('#show_jen_kel').text('Laki-laki');
                }

                // Tampilkan modal
                $('#modalShowKaryawan').modal('show');
            },
            error: function(xhr, status, error) {
                console.log("Error Status:", xhr.status);
                console.log("Response Text:", xhr.responseText);
                alert("Gagal mengambil data: " + xhr.status + " - " + error);
            }
        });
    }

    function destroyKaryawan(id) {
        // Update the form action to delete the correct employee
        $('#formDeleteKaryawan').attr('action', `/karyawan/${id}`);
        // Show the confirmation modal
        $('#modalDeleteKaryawan').modal('show');
    }
</script>

<script>
    // Menambahkan validasi menggunakan JavaScript
    document.addEventListener('DOMContentLoaded', function () {
        const formTambahKaryawan = document.querySelector('#formTambahKaryawan');

        formTambahKaryawan.addEventListener('submit', function (event) {
            // Cek validitas form
            if (!formTambahKaryawan.checkValidity()) {
                event.preventDefault(); // Mencegah form dikirim
                event.stopPropagation();

                // Tambahkan kelas 'was-validated' untuk menampilkan peringatan validasi
                formTambahKaryawan.classList.add('was-validated');
            }
        });
    });
</script>

@endsection
