<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class PenggajianController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(); // Inisialisasi Guzzle Client
    }

    /**
     * Menampilkan daftar penggajian untuk semua karyawan.
     */
    public function index(Request $request)
{
    $bulan = $request->query('bulan', now()->format('m')); // Default ke bulan sekarang
    $tahun = $request->query('tahun', now()->year); // Default ke tahun sekarang

    $url = config('api.base_url');
    $token = session('token'); // Ambil token dari session
    \Log::info('Parameter Bulan dan Tahun:', ['bulan' => $bulan, 'tahun' => $tahun]);

    try {
        $response = $this->client->request('GET', "{$url}/penggajian", [
            'query' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token, // Tambahkan header Authorization
            ],
        ]);

        $rekapGaji = json_decode($response->getBody()->getContents(), true);

       $data = $rekapGaji['data'] ?? $rekapGaji ?? [];

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;

        $paginated = new LengthAwarePaginator(
            collect($data)->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            count($data),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('content.gaji.view', [
            'rekapGaji' => $paginated,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
    } catch (\Exception $e) {
        \Log::error('Gagal memuat data penggajian: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Gagal memuat data.');
    }
}

    /**
     * Menampilkan penggajian berdasarkan karyawan.
     */

     public function showByKaryawan($id, Request $request)
{
    $url = config('api.base_url');
    $token = session('token'); // Ambil token dari session

    // Ambil parameter bulan dan tahun dari URL atau gunakan default
    $bulan = $request->query('bulan', now()->format('m')); // Default ke bulan saat ini
    $tahun = $request->query('tahun', now()->year);       // Default ke tahun saat ini

    Log::info("Memulai fungsi showByKaryawan untuk ID: {$id}, Bulan: {$bulan}, Tahun: {$tahun}");

    try {
        // Tambahkan parameter bulan dan tahun ke URL API
        $apiUrl = "{$url}/penggajian/{$id}?bulan={$bulan}&tahun={$tahun}";
        Log::info("Mengirim permintaan GET ke URL: {$apiUrl}");

        $response = $this->client->request('GET', $apiUrl, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token, // Tambahkan header Authorization
            ],
        ]);
        Log::info("Permintaan berhasil, menerima respon dari API.");

        $penggajian = json_decode($response->getBody()->getContents(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $jsonError = json_last_error_msg();
            Log::error("Error decoding JSON: {$jsonError}");
            throw new \Exception('Error decoding JSON: ' . $jsonError);
        }

        Log::info("Data penggajian berhasil didekode:", $penggajian);

        // Hitung total bonus dan potongan dari semua entri edit_gaji
        $totalBonus = 0;
        $totalPotongan = 0;
        $editGajiDetails = $penggajian['edit_gaji'] ?? [];

        foreach ($editGajiDetails as $edit) {
            if ($edit['jenis'] === 'bonus') {
                $totalBonus += $edit['jumlah'];
            } elseif ($edit['jenis'] === 'potongan') {
                $totalPotongan += $edit['jumlah'];
            }
        }

        // Kirim data ke view
        return view('content.gaji.detail', [
            'karyawan' => $penggajian['karyawan'] ?? [],
            'rekap' => [
                'bulan' => $penggajian['rekap']['bulan'] ?? null,
                'tahun' => $penggajian['rekap']['tahun'] ?? null,
                'total_gaji' => $penggajian['rekap']['total_gaji'] ?? 0,
                'jumlah_hari_kerja' => $penggajian['rekap']['jumlah_hari_kerja'] ?? 0,
                'total_jam' => $penggajian['rekap']['total_jam'] ?? 0,
                'total_bonus' => $penggajian['rekap']['total_bonus'] ?? 0,
                'total_potongan' => $penggajian['rekap']['total_potongan'] ?? 0,
                'edit_gaji_details' => $penggajian['rekap']['edit_gaji'] ?? [], // Semua entri edit_gaji
            ],
        ]);
    } catch (\Exception $e) {
        Log::error("Terjadi kesalahan: " . $e->getMessage());
        return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data: ' . $e->getMessage());
    }
}

    /**
     * Menampilkan penggajian berdasarkan bulan dan tahun untuk karyawan.
     */
    public function getByMonthYear($id_karyawan, Request $request)
    {
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        $url = config('api.base_url');
        $token = session('token');
        try {
            $response = $this->client->request('GET', "{$url}/penggajian/{$id_karyawan}/month-year", [
                'query' => ['bulan' => $bulan, 'tahun' => $tahun],
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $token, // Tambahkan header Authorization
                    ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Error decoding JSON: ' . json_last_error_msg());
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Menampilkan form untuk mengedit gaji karyawan.
     */
    public function formEditGaji($id_karyawan)
{
    $bulan = request('bulan', now()->format('m')); // Default ke bulan sekarang
    $tahun = request('tahun', now()->year);       // Default ke tahun sekarang

    $url = config('api.base_url');
    $token = session('token'); // Ambil token dari session
    try {
        // Tambahkan parameter bulan dan tahun ke URL API
        $apiUrl = "{$url}/penggajian/{$id_karyawan}?bulan={$bulan}&tahun={$tahun}";
        \Log::info("Mengirim permintaan GET ke URL: {$apiUrl}");

        $response = $this->client->request('GET', $apiUrl, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token, // Tambahkan header Authorization
            ],
        ]);
        $penggajian = json_decode($response->getBody()->getContents(), true);

        \Log::info('Respons API:', $penggajian);

        // Validasi apakah respons API valid
        if (empty($penggajian) || !isset($penggajian['karyawan'])) {
            return redirect()->route('penggajian.index')->with('error', 'Data penggajian tidak ditemukan.');
        }

        // Ambil informasi dari respons API
        $rekap = $penggajian; // Data utama
        $karyawan = $penggajian['karyawan']; // Sub-data karyawan

        return view('content.gaji.edit', compact('rekap', 'karyawan', 'bulan', 'tahun'));
    } catch (\Exception $e) {
        \Log::error('Terjadi kesalahan saat memuat data penggajian: ' . $e->getMessage());
        return redirect()->route('penggajian.index')->with('error', 'Gagal memuat data penggajian.');
    }
}

    /**
     * Menambahkan data edit gaji untuk karyawan (bonus/potongan).
     */
    public function tambahEditGaji(Request $request)
{
    \Log::info('Fungsi tambahEditGaji dipanggil.', $request->all());
    $request->validate([
        'id_karyawan' => 'required|integer',
        'jenis' => 'required|in:bonus,potongan',
        'jumlah' => 'required|numeric',
        'keterangan' => 'required|string|max:255',
    ]);

    $id_karyawan = $request->input('id_karyawan');
    $jenis = $request->input('jenis');
    $keterangan = $request->input('keterangan');
    $jumlah = $request->input('jumlah');
    $jumlah = (int) str_replace('.', '', $jumlah);

    $url = config('api.base_url');
    $token = session('token'); // Ambil token dari session
    \Log::info("Memulai proses tambah/edit gaji untuk karyawan ID: {$id_karyawan}");

    try {
        \Log::info("Mengirim data ke API:", [
            'url' => "{$url}/penggajian/edit-gaji",
            'payload' => [
                'id_karyawan' => $id_karyawan,
                'jenis' => $jenis,
                'jumlah' => $jumlah,
                'keterangan' => $keterangan,
            ],
        ]);

        $response = $this->client->request('POST', "{$url}/penggajian/edit-gaji", [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token, // Tambahkan header Authorization
            ],
            'json' => [
                'id_karyawan' => $id_karyawan,
                'jenis' => $jenis,
                'jumlah' => $jumlah,
                'keterangan' => $keterangan,
            ],
        ]);

        \Log::info("Respons dari API:", [
            'status_code' => $response->getStatusCode(),
            'body' => $response->getBody()->getContents(),
        ]);

        if ($response->getStatusCode() === 200) {
            \Log::info("Data berhasil diperbarui untuk karyawan ID: {$id_karyawan}");
            // Ambil parameter filter dan page dari request
            $bulan = $request->input('bulan');
            $tahun = $request->input('tahun');
            $page = $request->input('page', 1);

            return redirect()->route('penggajian.index', [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'page' => $page
            ])->with('success', 'Data berhasil diperbarui.');
        } else {
            throw new \Exception('Terjadi kesalahan saat memperbarui data.');
        }
    } catch (\Exception $e) {
        \Log::error("Terjadi kesalahan saat memproses tambah/edit gaji:", [
            'id_karyawan' => $id_karyawan,
            'error_message' => $e->getMessage(),
        ]);
        return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}
}
