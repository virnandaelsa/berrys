<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class PenjualanController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(); // Inisialisasi Guzzle Client
    }

    public function laporanDonat(Request $request)
{
    $tanggal = $request->input('tanggal', now()->format('Y-m-d')); // Default ke hari ini
    $url = config('api.base_url'); // Base URL API dari .env

    \Log::info('Masuk ke laporanDonat', ['tanggal' => $tanggal]);

    try {
        // Ambil data dari beberapa endpoint
        $jadwalResponse = $this->client->request('GET', "{$url}/jadwal", ['query' => ['tanggal' => $tanggal]]);
        $jadwal = json_decode($jadwalResponse->getBody()->getContents(), true)['data'] ?? [];
        \Log::info('Respons API Jadwal', ['data' => $jadwal]);

        $laporanDatangResponse = $this->client->request('GET', "{$url}/laporan/datang", ['query' => ['tanggal' => $tanggal]]);
        $laporanDatang = json_decode($laporanDatangResponse->getBody()->getContents(), true)['data'] ?? [];
        \Log::info('Respons API Laporan Datang', ['data' => $laporanDatang]);

        $stokDatangResponse = $this->client->request('GET', "{$url}/stok/datang", ['query' => ['tanggal' => $tanggal]]);
        $stokDatang = json_decode($stokDatangResponse->getBody()->getContents(), true)['data'] ?? [];
        \Log::info('Respons API Stok Datang', ['data' => $stokDatang]);

        $laporanPulangResponse = $this->client->request('GET', "{$url}/laporan/pulang", ['query' => ['tanggal' => $tanggal]]);
        $laporanPulang = json_decode($laporanPulangResponse->getBody()->getContents(), true)['data'] ?? [];
        \Log::info('Respons API Laporan Pulang', ['data' => $laporanPulang]);

        // Gabungkan data berdasarkan karyawan
        $karyawanData = $this->gabungkanData($jadwal, $laporanDatang, $stokDatang, $laporanPulang);
        \Log::info('Data yang dikirim ke view:', ['karyawanData' => $karyawanData]);

        return view('content.penjualan.laporan', [
            'tanggal' => $tanggal,
            'karyawanData' => $karyawanData,
        ]);
    } catch (\Exception $e) {
        \Log::error('Gagal memuat data laporan donat: ' . $e->getMessage());

        return view('content.penjualan.laporan', [
            'tanggal' => $tanggal,
            'karyawanData' => [],
            'error' => 'Gagal memuat data laporan donat.',
        ]);
    }
}

    /**
     * Fungsi untuk menggabungkan data berdasarkan karyawan.
     */
    private function gabungkanData($jadwal, $laporanDatang, $stokDatang, $laporanPulang)
    {
        $gabungan = [];

        // Buat peta id_jadwal ke id_karyawan dari data jadwal
        $mapJadwalToKaryawan = [];
        foreach ($jadwal as $item) {
            if (!isset($item['id']) || !isset($item['id_karyawan'])) {
                \Log::error('Data jadwal tidak memiliki id atau id_karyawan', ['item' => $item]);
                continue; // Lewati item yang tidak valid
            }
            $mapJadwalToKaryawan[$item['id']] = $item['id_karyawan'];
        }

        // Proses laporan datang
        foreach ($laporanDatang as $datang) {
            if (!isset($datang['id_jadwal'])) {
                \Log::error('Data laporan datang tidak memiliki id_jadwal', ['datang' => $datang]);
                continue; // Lewati item yang tidak valid
            }

            $idJadwal = $datang['id_jadwal'];
            if (!isset($gabungan[$idJadwal])) {
                $gabungan[$idJadwal] = $this->inisialisasiJadwal($jadwal, $idJadwal);
            }

            $gabungan[$idJadwal]['laporan_datang'] = [
                'bombo' => $datang['donat_bombo'] ?? 0,
                'bolong' => $datang['donat_bolong'] ?? 0,
                'salju' => $datang['donat_salju'] ?? 0,
            ];
        }

        // Proses stok datang
        foreach ($stokDatang as $stok) {
            if (!isset($stok['id_jadwal'])) {
                \Log::error('Data stok datang tidak memiliki id_jadwal', ['stok' => $stok]);
                continue; // Lewati item yang tidak valid
            }

            $idJadwal = $stok['id_jadwal'];
            if (!isset($gabungan[$idJadwal])) {
                $gabungan[$idJadwal] = $this->inisialisasiJadwal($jadwal, $idJadwal);
            }

            $gabungan[$idJadwal]['stok_datang_total']['bombo'] += $stok['donat_bombo'] ?? 0;
            $gabungan[$idJadwal]['stok_datang_total']['bolong'] += $stok['donat_bolong'] ?? 0;
            $gabungan[$idJadwal]['stok_datang_total']['salju'] += $stok['donat_salju'] ?? 0;
        }

        // Proses laporan pulang
        foreach ($laporanPulang as $pulang) {
            if (!isset($pulang['id_jadwal'])) {
                \Log::error('Data laporan pulang tidak memiliki id_jadwal', ['pulang' => $pulang]);
                continue; // Lewati item yang tidak valid
            }

            $idJadwal = $pulang['id_jadwal'];
            if (!isset($gabungan[$idJadwal])) {
                $gabungan[$idJadwal] = $this->inisialisasiJadwal($jadwal, $idJadwal);
            }

            $gabungan[$idJadwal]['laporan_pulang'] = [
                'bombo' => $pulang['stok_bomboloni'] ?? 0,
                'bolong' => $pulang['stok_bolong'] ?? 0,
                'salju' => $pulang['stok_salju'] ?? 0,
            ];
        }

        return array_values($gabungan);
    }

    private function inisialisasiJadwal($jadwal, $idJadwal)
    {
        foreach ($jadwal as $item) {
            if ($item['id'] == $idJadwal) {
                return [
                    'id_karyawan' => $item['id_karyawan'],
                    'id_jadwal' => $idJadwal,
                    'nama_karyawan' => $item['nama_karyawan'] ?? 'Unknown',
                    'shift' => $item['shift'] ?? 'Unknown',
                    'tempat' => $item['tempat'] ?? 'Unknown',
                    'laporan_datang' => [
                        'bombo' => 0,
                        'bolong' => 0,
                        'salju' => 0,
                    ],
                    'stok_datang_total' => [
                        'bombo' => 0,
                        'bolong' => 0,
                        'salju' => 0,
                    ],
                    'laporan_pulang' => [
                        'bombo' => 0,
                        'bolong' => 0,
                        'salju' => 0,
                    ],
                ];
            }
        }

        \Log::error('Jadwal dengan id_jadwal tidak ditemukan', ['id_jadwal' => $idJadwal]);
        return null;
    }

public function detail(Request $request)
{
    $idJadwal = $request->query('id_jadwal');
    $tanggal = $request->query('tanggal', now()->format('Y-m-d')); // Default ke hari ini
    $url = config('api.base_url'); // Base URL API dari .env

    if (!$idJadwal || !$tanggal) {
        \Log::error('Parameter id_jadwal atau tanggal tidak valid.', ['id_jadwal' => $idJadwal, 'tanggal' => $tanggal]);
        return view('content.penjualan.detail', [
            'id_jadwal' => $idJadwal,
            'tanggal' => $tanggal,
            'stokDetails' => [],
            'laporanAwal' => null,
            'laporanPulang' => null,
            'error' => 'Parameter id_jadwal atau tanggal tidak valid.',
        ]);
    }

    try {
        // Ambil data detail dari API
        \Log::info('Mengambil data detail stok dari API.', ['id_jadwal' => $idJadwal, 'tanggal' => $tanggal]);

        $response = $this->client->request('GET', "{$url}/stok/detail", [
            'query' => [
                'id_jadwal' => $idJadwal,
                'tanggal' => $tanggal,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        // Tambahkan log untuk mencatat data yang diterima dari API
        \Log::info('Data yang diterima dari API:', ['data' => $data]);


        if ($data['status'] !== 'success') {
            throw new \Exception($data['message'] ?? 'Gagal mengambil data dari API.');
        }

        $detailData = $data['data'];

        // Kirim data ke view
        return view('content.penjualan.detail', [
            'id_jadwal' => $idJadwal,
            'tanggal' => $tanggal,
            'laporanAwal' => $detailData['laporan_awal'] ?? null,
            'stokDetails' => $detailData['stok_datang'] ?? [],
            'laporanPulang' => $detailData['laporan_pulang'] ?? null,
        ]);
    } catch (\Exception $e) {
        \Log::error('Gagal memuat detail stok datang: ' . $e->getMessage());
        return view('content.penjualan.detail', [
            'id_jadwal' => $idJadwal,
            'tanggal' => $tanggal,
            'stokDetails' => [],
            'laporanAwal' => null,
            'laporanPulang' => null,
            'error' => 'Gagal memuat detail stok datang.',
        ]);
    }
}

    /**
     * Halaman Pendapatan
     */
    public function pendapatan(Request $request)
{
    $tanggal = $request->query('tanggal', now()->format('Y-m-d')); // Default ke hari ini
    $url = config('api.base_url');

    \Log::info('Parameter Tanggal:', ['tanggal' => $tanggal]);

    try {
        \Log::info('Mengambil data pendapatan dari API.', ['url' => "{$url}/pendapatan", 'tanggal' => $tanggal]);

        $response = $this->client->request('GET', "{$url}/pendapatan", [
            'query' => ['tanggal' => $tanggal],
        ]);

        $pendapatan = json_decode($response->getBody()->getContents(), true);

        if (empty($pendapatan['data'])) {
            \Log::info('Pendapatan tidak ditemukan untuk tanggal: ' . $tanggal);
            // Kirim pesan error ke view tanpa redirect
            return view('content.penjualan.pendapatan', [
                'pendapatan' => [], // Kosongkan data pendapatan
                'tanggal' => $tanggal,
                'error' => 'Tidak ada pendapatan untuk tanggal ini.',
            ]);
        }

        \Log::info('Pendapatan berhasil ditemukan.', ['data' => $pendapatan]);

        return view('content.penjualan.pendapatan', compact('pendapatan', 'tanggal'));
    } catch (\Exception $e) {
        \Log::error('Gagal memuat data pendapatan: ' . $e->getMessage());
        // Kirim pesan error ke view tanpa redirect
        return view('content.penjualan.pendapatan', [
            'pendapatan' => [], // Kosongkan data pendapatan
            'tanggal' => $tanggal,
            'error' => 'Gagal memuat data pendapatan.',
        ]);
    }
}
}
