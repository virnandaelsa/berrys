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
        // Get the input date or default to today if not provided
        $tanggal = $request->input('tanggal', now()->format('Y-m-d'));
        $url = config('api.base_url'); // Base URL API from .env
        $token = session('token'); // Get token from session

        \Log::info('Masuk ke laporanDonat', ['tanggal' => $tanggal]);

        try {
            // Request for jadwal
            $jadwalResponse = $this->client->request('GET', "{$url}/jadwal", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                'query' => ['tanggal_mulai' => $tanggal]
            ]);
            // 1. Ambil semua jadwal (tanpa filter tanggal)
            $jadwalAll = json_decode($jadwalResponse->getBody()->getContents(), true)['data'] ?? [];

            // 2. Buat mapping id_jadwal ke data karyawan
            $mapJadwal = [];
            foreach ($jadwalAll as $item) {
                $mapJadwal[$item['id']] = [
                    'id_karyawan' => $item['id_karyawan'] ?? null,
                    'nama_karyawan' => $item['nama_karyawan'] ?? 'Unknown',
                    'shift' => $item['shift'] ?? 'Unknown',
                    'tempat' => $item['tempat'] ?? 'Unknown',
                ];
            }

            // 3. Filter jadwal hanya untuk tanggal yang diminta
            $jadwal = array_values(array_filter($jadwalAll, function($item) use ($tanggal) {
                return isset($item['date']) && $item['date'] === $tanggal;
            }));

            \Log::info('Daftar ID jadwal yang diterima:', ['ids' => array_column($jadwal, 'id')]);
            \Log::info('Respons API Jadwal', ['data' => $jadwal]);

            // Request for laporan datang
            $laporanDatangResponse = $this->client->request('GET', "{$url}/laporan/datang", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                'query' => ['tanggal' => $tanggal]
            ]);
            $laporanDatang = json_decode($laporanDatangResponse->getBody()->getContents(), true)['data'] ?? [];
            \Log::info('Respons API Laporan Datang', ['data' => $laporanDatang]);

            // Request for stok datang (boleh gagal)
            try {
                $stokDatangResponse = $this->client->request('GET', "{$url}/stok/datang", [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $token,
                    ],
                    'query' => ['tanggal' => $tanggal]
                ]);
                $stokDatang = json_decode($stokDatangResponse->getBody()->getContents(), true)['data'] ?? [];
                \Log::info('Respons API Stok Datang', ['data' => $stokDatang]);
            } catch (\Exception $e) {
                \Log::warning('Stok datang tidak ditemukan atau error: ' . $e->getMessage());
                $stokDatang = [];
            }

            // Request for laporan pulang (boleh gagal)
            try {
                $laporanPulangResponse = $this->client->request('GET', "{$url}/laporan/pulang", [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $token,
                    ],
                    'query' => ['tanggal' => $tanggal]
                ]);
                $laporanPulang = json_decode($laporanPulangResponse->getBody()->getContents(), true)['data'] ?? [];
                \Log::info('Respons API Laporan Pulang', ['data' => $laporanPulang]);
            } catch (\Exception $e) {
                \Log::warning('Laporan pulang tidak ditemukan atau error: ' . $e->getMessage());
                $laporanPulang = [];
            }

            // Merge data based on karyawan
            $karyawanData = $this->gabungkanData($jadwal, $laporanDatang, $stokDatang, $laporanPulang, $mapJadwal);
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
    private function gabungkanData($jadwal, $laporanDatang, $stokDatang, $laporanPulang, $mapJadwal = [])
{
    $gabungan = [];

    if (!empty($jadwal)) {
        // ... INISIALISASI dan MERGE seperti biasa (kode kamu sebelumnya)
        foreach ($jadwal as $item) {
            $idJadwal = $item['id'];
            $gabungan[$idJadwal] = [
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
        // MERGE LAPORAN & STOK seperti biasa...
        foreach ($laporanDatang as $datang) {
            if (!isset($datang['id_jadwal'])) continue;
            $idJadwal = $datang['id_jadwal'];
            if (!isset($gabungan[$idJadwal])) continue;
            $gabungan[$idJadwal]['laporan_datang'] = [
                'bombo' => $datang['donat_bombo'] ?? 0,
                'bolong' => $datang['donat_bolong'] ?? 0,
                'salju' => $datang['donat_salju'] ?? 0,
            ];
        }
        foreach ($stokDatang as $stok) {
            if (!isset($stok['id_jadwal'])) continue;
            $idJadwal = $stok['id_jadwal'];
            if (!isset($gabungan[$idJadwal])) continue;
            $gabungan[$idJadwal]['stok_datang_total']['bombo'] += $stok['donat_bombo'] ?? 0;
            $gabungan[$idJadwal]['stok_datang_total']['bolong'] += $stok['donat_bolong'] ?? 0;
            $gabungan[$idJadwal]['stok_datang_total']['salju'] += $stok['donat_salju'] ?? 0;
        }
        foreach ($laporanPulang as $pulang) {
            if (!isset($pulang['id_jadwal'])) continue;
            $idJadwal = $pulang['id_jadwal'];
            if (!isset($gabungan[$idJadwal])) continue;
            $gabungan[$idJadwal]['laporan_pulang'] = [
                'bombo' => $pulang['stok_bomboloni'] ?? 0,
                'bolong' => $pulang['stok_bolong'] ?? 0,
                'salju' => $pulang['stok_salju'] ?? 0,
            ];
        }
    } else {
        // Untuk kasus jadwal kosong tetap gunakan mapping
        foreach ($laporanDatang as $datang) {
            if (!isset($datang['id_jadwal'])) continue;
            $idJadwal = $datang['id_jadwal'];
            $karyawan = $mapJadwal[$idJadwal] ?? [
                'id_karyawan' => null,
                'nama_karyawan' => 'Unknown',
                'shift' => 'Unknown',
                'tempat' => 'Unknown'
            ];
            $gabungan[$idJadwal] = [
                'id_karyawan' => $karyawan['id_karyawan'],
                'id_jadwal' => $idJadwal,
                'nama_karyawan' => $karyawan['nama_karyawan'],
                'shift' => $karyawan['shift'],
                'tempat' => $karyawan['tempat'],
                'laporan_datang' => [
                    'bombo' => $datang['donat_bombo'] ?? 0,
                    'bolong' => $datang['donat_bolong'] ?? 0,
                    'salju' => $datang['donat_salju'] ?? 0,
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
        foreach ($laporanPulang as $pulang) {
            if (!isset($pulang['id_jadwal'])) continue;
            $idJadwal = $pulang['id_jadwal'];
            $karyawan = $mapJadwal[$idJadwal] ?? [
                'id_karyawan' => null,
                'nama_karyawan' => 'Unknown',
                'shift' => 'Unknown',
                'tempat' => 'Unknown'
            ];
            if (!isset($gabungan[$idJadwal])) {
                $gabungan[$idJadwal] = [
                    'id_karyawan' => $karyawan['id_karyawan'],
                    'id_jadwal' => $idJadwal,
                    'nama_karyawan' => $karyawan['nama_karyawan'],
                    'shift' => $karyawan['shift'],
                    'tempat' => $karyawan['tempat'],
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
                        'bombo' => $pulang['stok_bomboloni'] ?? 0,
                        'bolong' => $pulang['stok_bolong'] ?? 0,
                        'salju' => $pulang['stok_salju'] ?? 0,
                    ],
                ];
            } else {
                $gabungan[$idJadwal]['laporan_pulang'] = [
                    'bombo' => $pulang['stok_bomboloni'] ?? 0,
                    'bolong' => $pulang['stok_bolong'] ?? 0,
                    'salju' => $pulang['stok_salju'] ?? 0,
                ];
            }
        }
    }

    // === FILTER HANYA YANG SUDAH INPUT LAPORAN ===
    $filtered = array_filter($gabungan, function ($item) {
        $datang = $item['laporan_datang'];
        $pulang = $item['laporan_pulang'];
        // Jika laporan datang ada isinya (salah satu > 0) ATAU laporan pulang ada isinya (salah satu > 0)
        return (
            ($datang['bombo'] ?? 0) > 0 ||
            ($datang['bolong'] ?? 0) > 0 ||
            ($datang['salju'] ?? 0) > 0 ||
            ($pulang['bombo'] ?? 0) > 0 ||
            ($pulang['bolong'] ?? 0) > 0 ||
            ($pulang['salju'] ?? 0) > 0
        );
    });

    return array_values($filtered);
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
    $token = session('token'); // Ambil token dari session

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
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token, // Tambahkan header Authorization
                ],
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
    $token = session('token'); // Ambil token dari session

    \Log::info('Parameter Tanggal:', ['tanggal' => $tanggal]);

    try {
        \Log::info('Mengambil data pendapatan dari API.', ['url' => "{$url}/pendapatan", 'tanggal' => $tanggal]);

        $response = $this->client->request('GET', "{$url}/pendapatan", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token, // Tambahkan header Authorization
                ],
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
