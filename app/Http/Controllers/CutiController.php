<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

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
    $token = session('token');

    $tanggal_mulai = $request->query('tanggal_mulai')
        ? Carbon::parse($request->query('tanggal_mulai'))->startOfWeek(Carbon::MONDAY)
        : now()->addWeek()->startOfWeek(Carbon::MONDAY);

    $tanggal_akhir = $tanggal_mulai->copy()->addDays(6);

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

        // Pengaturan Paginate Lokal
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $cutiCollection = collect($cutiData);
        $pagedCuti = new LengthAwarePaginator(
            $cutiCollection->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $cutiCollection->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

    } catch (\Exception $e) {
        \Log::error("Gagal mengambil data cuti: " . $e->getMessage());
        $pagedCuti = new LengthAwarePaginator([], 0, 10);
        return view('content.cuti.view', compact('pagedCuti', 'tanggal_mulai', 'tanggal_akhir'))
            ->with('error', 'Gagal mengambil data cuti.');
    }

    return view('content.cuti.view', compact('pagedCuti', 'tanggal_mulai', 'tanggal_akhir'));
}


    public function update(Request $request, $id)
{
    $url = config('api.base_url');
    $token = session('token');

    try {
        $response = $this->client->request('PUT', "{$url}/cuti/{$id}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
            'json' => $request->all(),
        ]);

        $karyawan = json_decode($response->getBody()->getContents(), true);

        // Ambil tanggal_mulai dari input/form
        $tanggalMulaiInput = $request->input('tanggal_mulai', now()->toDateString());
        // Parse ke Carbon dan cari hari Senin minggu itu
        $tanggalMulaiMinggu = \Carbon\Carbon::parse($tanggalMulaiInput)->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();

        return redirect()->route('cuti.index', ['tanggal_mulai' => $tanggalMulaiMinggu])
            ->with('success', 'Cuti berhasil diperbarui');
    } catch (\Exception $e) {
        \Log::error("Gagal memperbarui cuti: " . $e->getMessage());
        return redirect()->back()->with('error', 'Gagal memperbarui data cuti.');
    }
}
}
