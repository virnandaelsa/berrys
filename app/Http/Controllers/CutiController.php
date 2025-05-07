<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;

class CutiController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(); // Inisialisasi Guzzle Client
    }

    public function index(Request $request)
    {
        $url = config('api.base_url'); // Mendapatkan URL API dari config
        \Log::info("Mengambil data cuti dari: {$url}/cuti");

        // Ambil input tanggal dari request, default hari ini dan 6 hari ke depan
        $tanggal_mulai = $request->query('tanggal_mulai')
                ? Carbon::parse($request->query('tanggal_mulai'))->startOfWeek(Carbon::MONDAY)
                : now()->startOfWeek(Carbon::MONDAY);

        $tanggal_akhir = $tanggal_mulai->copy()->addDays(6);

        \Log::info("Filter jadwal dari $tanggal_mulai sampai $tanggal_akhir");

        try {
            // Panggil API dengan query tanggal
            $response = $this->client->request('GET', "{$url}/cuti", [
                'query' => [
                    'tanggal_mulai' => $tanggal_mulai->toDateString(),
                    'tanggal_akhir' => $tanggal_akhir->toDateString()
                ]
            ]);

            $cutiData = json_decode($response->getBody()->getContents(), true)['data'] ?? [];

            // Log response dari API
            \Log::info("Response API: ", ['response' => $cutiData]);

        } catch (\Exception $e) {
            \Log::error("Gagal mengambil data cuti: " . $e->getMessage());

            $cutiData = []; // Set default kosong jika gagal
            return view('content.cuti.view', compact('cutiData', 'tanggal_mulai', 'tanggal_akhir'))
                ->with('error', 'Gagal mengambil data cuti.');

        }
        return view('content.cuti.view', compact('cutiData', 'tanggal_mulai', 'tanggal_akhir'));
    }

    public function update(Request $request, $id)
    {
        $url = config('api.base_url'); // Mendapatkan URL API dari config

        try {
            // Mengirimkan data melalui PUT request untuk mengupdate cuti
            $response = $this->client->request('PUT', "{$url}/cuti/{$id}", [
                'json' => $request->all(), // Mengirimkan data form yang diperbarui
            ]);

            // Mengambil response untuk melihat apakah data berhasil diperbarui
            $karyawan = json_decode($response->getBody()->getContents(), true);

            // Ambil tanggal dari request (default ke hari ini jika tidak ada)
            $tanggal_mulai = $request->input('tanggal_mulai', now()->toDateString());

            // Redirect ke halaman index dengan parameter tanggal
            return redirect()->route('cuti.index', ['tanggal_mulai' => $tanggal_mulai])
                ->with('success', 'Cuti berhasil diperbarui');

        } catch (\Exception $e) {
            \Log::error("Gagal memperbarui cuti: " . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal memperbarui data cuti.');
        }
    }

}
