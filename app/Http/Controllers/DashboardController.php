<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(); // Inisialisasi Guzzle Client
    }

    public function dashboard(Request $request)
    {
        try {
            \Log::info('Memulai proses pengambilan data untuk dashboard.');

            $url = config('api.base_url');
            $tahun = now()->year;
            $token = session('token'); // Ambil token dari session

            \Log::info("Base URL API: {$url}, Tahun: {$tahun}");

            // Log token untuk debugging
            if ($token) {
                Log::info('Token yang digunakan untuk API:', ['token' => $token]);
            } else {
                Log::warning('Token tidak ditemukan di session.');
                return back()->with('error', 'Token tidak ditemukan. Silakan login kembali.');
            }

            // Ambil data Dashboard Admin
            \Log::info('Mengirim permintaan ke API /dashboard/admin.');
            $adminResponse = $this->client->get("{$url}/dashboard/admin", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token, // Tambahkan header Authorization
                    'Accept' => 'application/json',
                ],
            ]);
            \Log::info('Berhasil menerima respon dari API /dashboard/admin.');

            $dashboardData = json_decode($adminResponse->getBody()->getContents(), true);

            if ($dashboardData['status'] !== 'success') {
                \Log::error('API /dashboard/admin mengembalikan status gagal.', [
                    'response' => $dashboardData
                ]);
                return back()->with('error', 'Gagal mengambil data dashboard admin.');
            }
            \Log::info('Data Dashboard Admin berhasil diproses.', ['data' => $dashboardData]);

            // Ambil data Pendapatan Tahunan
            \Log::info('Mengirim permintaan ke API /pendapatan/tahunan.');
            $pendapatanResponse = $this->client->get("{$url}/pendapatan/tahunan", [
                'query' => ['tahun' => $tahun],
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token, // Tambahkan header Authorization
                ],
            ]);
            \Log::info('Berhasil menerima respon dari API /pendapatan/tahunan.');

            $pendapatanData = json_decode($pendapatanResponse->getBody()->getContents(), true);

            if ($pendapatanData['status'] !== 'success') {
                \Log::error('API /pendapatan/tahunan mengembalikan status gagal.', [
                    'response' => $pendapatanData
                ]);
                return back()->with('error', 'Gagal mengambil data pendapatan tahunan.');
            }
            \Log::info('Data Pendapatan Tahunan berhasil diproses.', ['data' => $pendapatanData]);

            // Proses data pendapatan
            $pendapatan = $pendapatanData['data']['pendapatan'] ?? array_fill(1, 12, 0);
            \Log::info('Pendapatan per bulan:', ['pendapatan' => $pendapatan]);

            // Kirim data ke view
            \Log::info('Mengirim data ke view content.dashboard.dashboard.');
            return view('content.dashboard.dashboard', [
                'dashboardData' => $dashboardData['data'],
                'pendapatan' => $pendapatan,
                'tahun' => $tahun,
            ]);

        } catch (\Exception $e) {
            \Log::error('Gagal mengambil data dashboard:', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat mengambil data dashboard.');
        }
    }
}
