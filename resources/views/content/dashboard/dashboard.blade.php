@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}">
@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
@endsection

@section('content')
<div class="row gy-4">
  <!-- Selamat Datang Card -->
  <div class="col-12">
    <div class="card text-center p-4 bg-primary text-white">
        <h3 class="fw-bold text-white">Selamat Datang, Owner Berry's Bakery!</h3>
      <p class="mb-0">Berikut adalah ringkasan performa bisnis Anda</p>
    </div>
  </div>

  <!-- Statistik Karyawan -->
  <div class="col-md-3 col-6">
    <div class="card text-center p-4 shadow-sm">
      <h6 class="text-muted">Jumlah Karyawan Aktif</h6>
      <h3 class="fw-bold text-primary">{{ $dashboardData['karyawan_aktif'] ?? 0 }}</h3>
    </div>
  </div>

  <div class="col-md-3 col-6">
    <div class="card text-center p-4 shadow-sm">
      <h6 class="text-muted">Jumlah Karyawan Masuk</h6>
      <h3 class="fw-bold text-success">{{ $dashboardData['jumlah_karyawan_masuk']['jumlah'] ?? 0 }}</h3>
    </div>
  </div>

  <div class="col-md-3 col-6">
    <div class="card text-center p-4 shadow-sm">
      <h6 class="text-muted">Jumlah Karyawan Absensi</h6>
      <h3 class="fw-bold text-danger">{{ $dashboardData['jumlah_karyawan_absen']['jumlah'] ?? 0 }}</h3>
    </div>
  </div>

  <div class="col-md-3 col-6">
    <div class="card text-center p-4 shadow-sm">
      <h6 class="text-muted">Total Gaji</h6>
      <h3 class="fw-bold text-warning">Rp {{ number_format($dashboardData['total_penggajian']['total'] ?? 0, 0, ',', '.') }}</h3>
    </div>
  </div>

  <!-- Grafik Pendapatan -->
  <div class="col-lg-12">
    <div class="card shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">Grafik Pendapatan Bulanan Tahun {{ $tahun }}</h5>
        <small class="text-muted">Sumber data: Sistem</small>
      </div>
      <div class="card-body">
        <div id="revenueChart"></div>
      </div>
    </div>
  </div>
</div>

<!-- Script untuk Grafik -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    var options = {
      chart: {
        type: 'bar',
        height: 350,
        toolbar: {
          show: true
        },
        animations: {
          enabled: true,
          easing: 'easeinout',
          speed: 800
        }
      },
      series: [{
        name: 'Pendapatan',
        data: @json(array_values($pendapatan)) // Data pendapatan per bulan dari backend
      }],
      xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'], // Nama bulan
        title: {
          text: 'Bulan'
        }
      },
      yaxis: {
        title: {
          text: 'Pendapatan (Rp)'
        },
        labels: {
          formatter: function (value) {
            return 'Rp ' + value.toLocaleString('id-ID');
          }
        }
      },
      colors: ['#FF5733'], // Warna batang grafik
      dataLabels: {
        enabled: true,
        formatter: function (val) {
          return 'Rp ' + val.toLocaleString('id-ID');
        },
        offsetY: -20,
        style: {
          fontSize: '12px',
          colors: ['#304758']
        }
      },
      tooltip: {
        y: {
          formatter: function (value) {
            return 'Rp ' + value.toLocaleString('id-ID');
          }
        }
      },
      grid: {
        borderColor: '#f1f1f1',
        padding: {
          left: 10,
          right: 10
        }
      },
      title: {
        text: 'Pendapatan Bulanan',
        align: 'left',
        margin: 10,
        style: {
          fontSize: '16px',
          fontWeight: 'bold',
          color: '#263238'
        }
      }
    };

    var chart = new ApexCharts(document.querySelector("#revenueChart"), options);
    chart.render();
  });
</script>
@endsection
