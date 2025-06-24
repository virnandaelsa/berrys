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
    <th>Face Deteksi Datang</th>
    <th>Face Deteksi Pulang</th>
</tr>
</thead>
<tbody>
@forelse($karyawanData as $key => $karyawan)
    <tr>
        <td>{{ ($karyawanData->firstItem() ?? 0) + $key }}</td>
        <td>{{ $karyawan['karyawan']['nama'] ?? 'Tidak tersedia' }}</td>
        <td>{{ $karyawan['absensi'][0]['jadwal']['tempat'] ?? 'Tidak tersedia' }}</td>
        <td>{{ $karyawan['absensi'][0]['jadwal']['shift'] ?? 'Tidak tersedia' }}</td>

        {{-- Jam Datang --}}
        <td>
            @if(isset($karyawan['absensi'][0]))
                {{ \Carbon\Carbon::parse($karyawan['absensi'][0]['jam'])->format('H:i') }}
            @else
                -
            @endif
        </td>
        {{-- Jam Pulang --}}
        <td>
            @if(isset($karyawan['absensi'][1]))
                {{ \Carbon\Carbon::parse($karyawan['absensi'][1]['jam'])->format('H:i') }}
            @else
                -
            @endif
        </td>

        {{-- Foto Datang --}}
        <td>
            @if(isset($karyawan['absensi'][0]['photo']) && $karyawan['absensi'][0]['photo'])
                <a href="#" data-bs-toggle="modal" data-bs-target="#fotoModalDatang{{ $karyawan['absensi'][0]['photo'] }}">
                    <img src="{{ config('api.photo_url') . '/' . $karyawan['absensi'][0]['photo'] }}" alt="Foto Datang" width="100" height="100">
                </a>
            @else
                <span>No Photo</span>
            @endif
        </td>
        {{-- Foto Pulang --}}
        <td>
            @if(isset($karyawan['absensi'][1]['photo']) && $karyawan['absensi'][1]['photo'])
                <a href="#" data-bs-toggle="modal" data-bs-target="#fotoModalPulang{{ $karyawan['absensi'][1]['photo'] }}">
                    <img src="{{ config('api.photo_url') . '/' . $karyawan['absensi'][1]['photo'] }}" alt="Foto Pulang" width="100" height="100">
                </a>
            @else
                <span>No Photo</span>
            @endif
        </td>
        {{-- Face Deteksi Datang --}}
        <td>
            @if(isset($karyawan['absensi'][0]['facePhoto']) && $karyawan['absensi'][0]['facePhoto'])
                <a href="#" data-bs-toggle="modal" data-bs-target="#faceModalDatang{{ $karyawan['absensi'][0]['facePhoto'] }}">
                    <img src="{{ config('api.photo_url') . '/' . $karyawan['absensi'][0]['facePhoto'] }}" alt="Face Deteksi Datang" width="100" height="100">
                </a>
            @else
                <span>No Face Photo</span>
            @endif
        </td>
        {{-- Face Deteksi Pulang --}}
        <td>
            @if(isset($karyawan['absensi'][1]['facePhoto']) && $karyawan['absensi'][1]['facePhoto'])
                <a href="#" data-bs-toggle="modal" data-bs-target="#faceModalPulang{{ $karyawan['absensi'][1]['facePhoto'] }}">
                    <img src="{{ config('api.photo_url') . '/' . $karyawan['absensi'][1]['facePhoto'] }}" alt="Face Deteksi Pulang" width="100" height="100">
                </a>
            @else
                <span>No Face Photo</span>
            @endif
        </td>
    </tr>

    {{-- Modal Foto Datang --}}
    @if(isset($karyawan['absensi'][0]['photo']) && $karyawan['absensi'][0]['photo'])
        <div class="modal fade" id="fotoModalDatang{{ $karyawan['absensi'][0]['photo'] }}" tabindex="-1" aria-labelledby="fotoModalDatangLabel{{ $karyawan['absensi'][0]['photo'] }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="fotoModalDatangLabel{{ $karyawan['absensi'][0]['photo'] }}">Foto Datang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <img src="{{ config('api.photo_url') . '/' . $karyawan['absensi'][0]['photo'] }}" alt="Foto Datang" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Foto Pulang --}}
    @if(isset($karyawan['absensi'][1]['photo']) && $karyawan['absensi'][1]['photo'])
        <div class="modal fade" id="fotoModalPulang{{ $karyawan['absensi'][1]['photo'] }}" tabindex="-1" aria-labelledby="fotoModalPulangLabel{{ $karyawan['absensi'][1]['photo'] }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="fotoModalPulangLabel{{ $karyawan['absensi'][1]['photo'] }}">Foto Pulang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <img src="{{ config('api.photo_url') . '/' . $karyawan['absensi'][1]['photo'] }}" alt="Foto Pulang" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Face Deteksi Datang --}}
    @if(isset($karyawan['absensi'][0]['facePhoto']) && $karyawan['absensi'][0]['facePhoto'])
        <div class="modal fade" id="faceModalDatang{{ $karyawan['absensi'][0]['facePhoto'] }}" tabindex="-1" aria-labelledby="faceModalDatangLabel{{ $karyawan['absensi'][0]['facePhoto'] }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="faceModalDatangLabel{{ $karyawan['absensi'][0]['facePhoto'] }}">Face Deteksi Datang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <img src="{{ config('api.photo_url') . '/' . $karyawan['absensi'][0]['facePhoto'] }}" alt="Face Deteksi Datang" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Face Deteksi Pulang --}}
    @if(isset($karyawan['absensi'][1]['facePhoto']) && $karyawan['absensi'][1]['facePhoto'])
        <div class="modal fade" id="faceModalPulang{{ $karyawan['absensi'][1]['facePhoto'] }}" tabindex="-1" aria-labelledby="faceModalPulangLabel{{ $karyawan['absensi'][1]['facePhoto'] }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="faceModalPulangLabel{{ $karyawan['absensi'][1]['facePhoto'] }}">Face Deteksi Pulang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <img src="{{ config('api.photo_url') . '/' . $karyawan['absensi'][1]['facePhoto'] }}" alt="Face Deteksi Pulang" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    @endif

@empty
    <tr>
        <td colspan="10" class="text-center">Tidak ada data absensi untuk tanggal {{ \Carbon\Carbon::parse($tanggal)->format('d-m-Y') }}.</td>
    </tr>
@endforelse
</tbody>
    </table>
 {{ $karyawanData->appends(request()->query())->links('pagination::bootstrap-5') }}
</div>

@endsection
