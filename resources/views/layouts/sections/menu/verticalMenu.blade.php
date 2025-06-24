<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo text-center py-3">
    <a href="{{ url('/') }}" class="app-brand-link d-flex align-items-center justify-content-center">
      <img src="{{ asset('storage/berrys.png') }}" alt="Logo" width="50" height="40">
      <span class="app-brand-text demo menu-text fw-bold ms-2">Berry's Bakery</span>
    </a>
  </div>

  <ul class="menu-inner py-2">
    <!-- Dashboard -->
    <li class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
      <a href="{{ url('/dashboard') }}" class="menu-link">
        <i class="mdi mdi-view-dashboard-outline"></i>
        <div>Dashboard</div>
      </a>
    </li>

    <li class="menu-item {{ request()->is('penjualan/*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="mdi mdi-cash-multiple"></i>
        <div>Laporan Penjualan</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->is('penjualan/laporan-donat') ? 'active' : '' }}">
          <a href="{{ url('/penjualan/laporan-donat') }}" class="menu-link">
            <div>Stok</div>
          </a>
        </li>
        <li class="menu-item {{ request()->is('penjualan/pendapatan') ? 'active' : '' }}">
          <a href="{{ url('/penjualan/pendapatan') }}" class="menu-link">
            <div>Pendapatan</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Jadwal -->
    <li class="menu-item {{ request()->is('jadwal') ? 'active' : '' }}">
      <a href="{{ url('/jadwal') }}" class="menu-link">
        <i class="mdi mdi-calendar"></i>
        <div>Jadwal</div>
      </a>
    </li>

    <!-- Absensi -->
    <li class="menu-item {{ request()->is('absensi') ? 'active' : '' }}">
      <a href="{{ url('/absensi') }}" class="menu-link">
        <i class="mdi mdi-account-check"></i>
        <div>Absensi</div>
      </a>
    </li>

    <!-- Gaji -->
    <li class="menu-item {{ request()->is('penggajian') ? 'active' : '' }}">
      <a href="{{ url('/penggajian') }}" class="menu-link">
        <i class="mdi mdi-cash"></i>
        <div>Gaji</div>
      </a>
    </li>

    <!-- Karyawan (Submenu) -->
    <li class="menu-item {{ request()->is('karyawan/*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="mdi mdi-account-group"></i>
        <div>Karyawan</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->is('karyawan/aktif') ? 'active' : '' }}">
          <a href="{{ url('/karyawan/aktif') }}" class="menu-link">
            <div>Aktif</div>
          </a>
        </li>
        <li class="menu-item {{ request()->is('karyawan/riwayat') ? 'active' : '' }}">
          <a href="{{ url('/karyawan/riwayat') }}" class="menu-link">
            <div>Riwayat</div>
          </a>
        </li>
      </ul>
    </li>
  </ul>
</aside>
