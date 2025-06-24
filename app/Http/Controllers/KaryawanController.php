<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Pagination\LengthAwarePaginator;

class KaryawanController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(); // Inisialisasi Guzzle Client
    }

    public function index()
{
    $url = config('api.base_url'); // Mendapatkan URL API dari config
    $token = session('token'); // Ambil token dari session


    // Log token untuk debugging
    if ($token) {
        Log::info('Token yang digunakan untuk API:', ['token' => $token]);
    } else {
        Log::warning('Token tidak ditemukan di session.');
        return back()->with('error', 'Token tidak ditemukan. Silakan login kembali.');
    }

    try {
        // Menambahkan header Authorization dengan token dari session
        $response = $this->client->request('GET', "{$url}/karyawan", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ]
        ]);

        // Log respons dari API untuk debugging
        $karyawanData = json_decode($response->getBody()->getContents(), true);
        Log::info('Respons dari API:', ['response' => $karyawanData]);

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;

        $karyawanAktif = collect($karyawanData['data'])->where('status', 'Aktif')->values();
        $pagedKaryawanAktif = new LengthAwarePaginator(
            $karyawanAktif->slice(($currentPage - 1) * $perPage, $perPage)->values(),
            $karyawanAktif->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('content.karyawan.index', ['karyawanAktif' => $pagedKaryawanAktif]);

    } catch (\GuzzleHttp\Exception\ClientException $e) {
        // Log kesalahan jika terjadi
        Log::error('Kesalahan saat mengakses API:', [
            'message' => $e->getMessage(),
            'status_code' => $e->getResponse() ? $e->getResponse()->getStatusCode() : null,
        ]);

        // Tangani kesalahan, misalnya dengan mengarahkan kembali dengan pesan kesalahan
        return back()->with('error', 'Gagal mengambil data karyawan. Silakan coba lagi.');
    }
}


    public function riwayat()
{
    $url = config('api.base_url');
    $token = session('token'); // Ambil token dari session
    Log::info("Fungsi riwayat() dipanggil.");
    Log::info("Base URL: {$url}");

    Log::info("Mengakses URL: {$url}/karyawan");
    $response = $this->client->request('GET', "{$url}/karyawan", [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ]
    ]);
    Log::info('Respons API berhasil diterima.', ['status' => $response->getStatusCode()]);

    $karyawanData = json_decode($response->getBody()->getContents(), true);
    Log::info('Data karyawan berhasil di-decode.', ['data' => $karyawanData]);

    $karyawanTidakAktif = collect($karyawanData['data'])->where('status', 'Tidak Aktif')->values();

    // PAGINATE 10 per halaman
    $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
    $perPage = 10;
    $pagedKaryawanTidakAktif = new \Illuminate\Pagination\LengthAwarePaginator(
        $karyawanTidakAktif->slice(($currentPage - 1) * $perPage, $perPage)->values(),
        $karyawanTidakAktif->count(),
        $perPage,
        $currentPage,
        ['path' => request()->url(), 'query' => request()->query()]
    );

    Log::info('Data karyawan tidak aktif berhasil dipaginate.', ['page_data' => $pagedKaryawanTidakAktif]);

    return view('content.karyawan.riwayat', ['karyawanTidakAktif' => $pagedKaryawanTidakAktif]);
}

    public function show($id)
{
    $url = config('api.base_url');
    $token = session('token');
    $endpoint = "{$url}/karyawan/{$id}";

    try {
        $response = $this->client->request('GET', $endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ]
        ]);
        $body = json_decode($response->getBody()->getContents(), true);

        \Carbon\Carbon::setLocale('id');

        if (isset($body['data'])) {
            if (isset($body['data']['tanggal_lahir'])) {
                $body['data']['tanggal_lahir'] = \Carbon\Carbon::parse($body['data']['tanggal_lahir'])->format('Y-m-d');
                $body['data']['tanggal_lahir_formatted'] = \Carbon\Carbon::parse($body['data']['tanggal_lahir'])->translatedFormat('d F Y');
            }
            if (isset($body['data']['tanggal_masuk'])) {
                $body['data']['tanggal_masuk'] = \Carbon\Carbon::parse($body['data']['tanggal_masuk'])->format('Y-m-d');
                $body['data']['tanggal_masuk_formatted'] = \Carbon\Carbon::parse($body['data']['tanggal_masuk'])->translatedFormat('d F Y');
            }
        }

        \Log::info("Respons dari API (after format):", $body);

        return response()->json($body);
    } catch (\Exception $e) {
        \Log::error("Gagal mengambil data dari API: {$e->getMessage()}");
        return response()->json(['error' => 'Unable to fetch data'], 500);
    }
}

    public function store(Request $request)
{
    $url = config('api.base_url');
    $token = session('token');

    try {
        $response = $this->client->request('POST', "{$url}/karyawan", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
            'json' => $request->all(),
            'timeout' => 10,
        ]);

        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil ditambahkan');
    } catch (ClientException | RequestException $e) { // Tangkap dua-duanya
        if ($e->hasResponse() && $e->getResponse()->getStatusCode() === 422) {
            $responseBody = json_decode($e->getResponse()->getBody()->getContents(), true);
            $errors = $responseBody['error'] ?? ['Terjadi kesalahan validasi.'];
            return back()->withErrors($errors)->withInput();
        }
        return back()->with('error', 'Gagal menambahkan karyawan. ' . $e->getMessage());
    } catch (\Exception $e) {
        return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

    public function edit($id)
    {
        $url = config('api.base_url');

        try {
            $response = $this->client->request('GET', "{$url}/karyawan/{$id}", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . session('api_token'),
                ],
            ]);

            $karyawan = json_decode($response->getBody()->getContents(), true);

            return response()->json($karyawan);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return response()->json(['error' => 'Gagal mengambil data karyawan.'], 500);
        }
    }

    public function update(Request $request, $id)
{
    $url = config('api.base_url');
    $token = session('token'); // Pastikan token dari session

    try {
        $response = $this->client->request('PUT', "{$url}/karyawan/{$id}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
            'json' => $request->all(),
        ]);

        $karyawan = json_decode($response->getBody()->getContents(), true);

        $page = $request->input('page', 1);
        $from = $request->input('from', 'index'); // default ke index jika tidak ada

        // Redirect sesuai asal form
        if ($from === 'riwayat') {
            return redirect()->route('karyawan.riwayat', ['page' => $page])
                ->with('success', 'Karyawan berhasil diperbarui');
        } else {
            return redirect()->route('karyawan.index', ['page' => $page])
                ->with('success', 'Karyawan berhasil diperbarui');
        }
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        return back()->with('error', 'Gagal memperbarui karyawan. ' . $e->getMessage());
    }
}

    public function destroy($id)
    {
        $url = config('api.base_url'); // Mendapatkan URL API dari config

        // Mengirimkan request DELETE ke API untuk menghapus data karyawan
        $response = $this->client->request('DELETE', "{$url}/karyawan/{$id}");

        // Mengambil response untuk memastikan bahwa data berhasil dihapus
        $result = json_decode($response->getBody()->getContents(), true);

        // Redirect ke halaman index setelah data dihapus
        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil dihapus');
    }

}
