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
        $url = config('api.base_url');
        $token = session('token'); // Ambil token dari session

        \Log::info("Mengambil data cuti dari: {$url}/cuti");

        $tanggal_mulai = $request->query('tanggal_mulai')
            ? Carbon::parse($request->query('tanggal_mulai'))->startOfWeek(Carbon::MONDAY)
            : now()->addWeek()->startOfWeek(Carbon::MONDAY);

        $tanggal_akhir = $tanggal_mulai->copy()->addDays(6);

        \Log::info("Filter jadwal dari $tanggal_mulai sampai $tanggal_akhir");

        try {
            $response = $this->client->request('GET', "{$url}/cuti", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
                'query' => [
                    'tanggal_mulai' => $tanggal_mulai->toDateString(),
                    'tanggal_akhir' => $tanggal_akhir->toDateString()
                ]
            ]);

            $cutiData = json_decode($response->getBody()->getContents(), true)['data'] ?? [];
            \Log::info("Response API: ", ['response' => $cutiData]);

        } catch (\Exception $e) {
            \Log::error("Gagal mengambil data cuti: " . $e->getMessage());

            $cutiData = [];
            return view('content.cuti.view', compact('cutiData', 'tanggal_mulai', 'tanggal_akhir'))
                ->with('error', 'Gagal mengambil data cuti.');
        }

        return view('content.cuti.view', compact('cutiData', 'tanggal_mulai', 'tanggal_akhir'));
    }

    public function update(Request $request, $id)
    {
        $url = config('api.base_url');
        $token = session('token'); // Ambil token dari session

        try {
            $response = $this->client->request('PUT', "{$url}/cuti/{$id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
                'json' => $request->all(),
            ]);

            $karyawan = json_decode($response->getBody()->getContents(), true);
            $tanggal_mulai = $request->input('tanggal_mulai', now()->toDateString());

            return redirect()->route('cuti.index', ['tanggal_mulai' => $tanggal_mulai])
                ->with('success', 'Cuti berhasil diperbarui');

        } catch (\Exception $e) {
            \Log::error("Gagal memperbarui cuti: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui data cuti.');
        }
    }
}
