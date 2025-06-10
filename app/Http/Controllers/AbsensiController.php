<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class AbsensiController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(); // Guzzle HTTP Client
    }

    public function index(Request $request)
    {
        // Ambil tanggal dari query string, default ke hari ini jika tidak ada
        $tanggal = $request->query('tanggal', \Carbon\Carbon::today()->toDateString());
        $formattedTanggal = \Carbon\Carbon::parse($tanggal)->format('Y-m-d');

        // Mendapatkan URL API dari config
        $url = config('api.base_url');
        $token = session('token'); // Ambil token dari session
        Log::info('Memulai proses pengambilan data absensi', ['tanggal' => $formattedTanggal, 'url' => $url]);

        try {
            // Ambil data absensi dari API
            $response = $this->client->request('GET', "{$url}/absensi", [
                'headers' =>[
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ]
            ]);

            // Decode data JSON yang diterima
            $responseData = $response->getBody()->getContents();
            $karyawanData = json_decode($responseData, true);

            // Log isi respons
            Log::debug('Response API absensi diterima', ['raw_response' => $responseData]);

            // Pastikan respons JSON memiliki key 'data'
            if (!isset($karyawanData['data'])) {
                Log::warning('Key "data" tidak ditemukan dalam response API', ['response' => $karyawanData]);
                throw new \Exception('Respons API tidak memiliki key "data".');
            }

            $karyawanData = $karyawanData['data'];
            Log::info('Data absensi berhasil diambil', ['jumlah_data' => count($karyawanData)]);

        } catch (\Exception $e) {
            Log::error('Gagal mengambil data absensi dari API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('content.absensi.view', [
                'karyawanData' => [],
                'tanggal' => $formattedTanggal,
            ])->with('message', 'Gagal mengambil data absensi. Silakan coba lagi.');
        }

        // Filter absensi berdasarkan tanggal
        $filtered = array_values(array_filter($karyawanData, function ($absen) use ($formattedTanggal) {
            return isset($absen['tanggal']) &&
                \Carbon\Carbon::parse($absen['tanggal'])->isSameDay(\Carbon\Carbon::parse($formattedTanggal));
        }));

        Log::info('Data absensi difilter berdasarkan tanggal', [
            'tanggal' => $formattedTanggal,
            'jumlah_data_setelah_filter' => count($filtered)
        ]);

        // Kelompokkan berdasarkan ID karyawan
        $groupedData = [];
        foreach ($filtered as $absen) {
            $idKaryawan = $absen['karyawan']['id'] ?? null;
            if (!$idKaryawan) {
                Log::warning('Data absensi tanpa ID karyawan ditemukan', ['data' => $absen]);
                continue;
            }

            if (!isset($groupedData[$idKaryawan])) {
                $groupedData[$idKaryawan] = [
                    'karyawan' => $absen['karyawan'],
                    'absensi' => [],
                ];
            }

            $groupedData[$idKaryawan]['absensi'][] = $absen;
        }

        Log::info('Data absensi berhasil dikelompokkan berdasarkan karyawan', [
            'jumlah_karyawan' => count($groupedData)
        ]);

        // PAGINATE: 10 karyawan per halaman
        $groupedCollection = collect($groupedData)->values();
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $pagedData = $groupedCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedKaryawan = new LengthAwarePaginator(
            $pagedData,
            $groupedCollection->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Kirim ke view
        return view('content.absensi.view', [
            'karyawanData' => $paginatedKaryawan,
            'tanggal' => $formattedTanggal,
        ]);
    }
}
