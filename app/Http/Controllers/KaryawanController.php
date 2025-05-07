<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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


    dd($token);


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
                'Authorization' => 'Bearer ' . $token
            ]
        ]);

        // Log respons dari API untuk debugging
        $karyawanData = json_decode($response->getBody()->getContents(), true);
        Log::info('Respons dari API:', ['response' => $karyawanData]);

        // Filter data karyawan
        $karyawanAktif = collect($karyawanData['data'])->where('status', 'Aktif')->values();
        $karyawanTidakAktif = collect($karyawanData['data'])->where('status', 'Tidak Aktif')->values();

        // Passing data karyawan aktif ke blade utama
        return view('content.karyawan.index', compact('karyawanAktif'));

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
        Log::info("Fungsi riwayat() dipanggil.");
        Log::info("Base URL: {$url}");

        Log::info("Mengakses URL: {$url}/karyawan");
        $response = $this->client->request('GET', "{$url}/karyawan");
        Log::info('Respons API berhasil diterima.', ['status' => $response->getStatusCode()]);

        $karyawanData = json_decode($response->getBody()->getContents(), true);
        Log::info('Data karyawan berhasil di-decode.', ['data' => $karyawanData]);

        $karyawanTidakAktif = collect($karyawanData['data'])->where('status', 'Tidak Aktif')->values();
        Log::info('Data karyawan tidak aktif berhasil difilter.', ['data_tidak_aktif' => $karyawanTidakAktif]);

        return view('content.karyawan.riwayat', compact('karyawanTidakAktif'));
    }

    public function show($id)
    {
        $url = config('api.base_url');
        $endpoint = "{$url}/karyawan/{$id}";

        \Log::info("Mengambil data dari endpoint: {$endpoint}");

        try {
            $response = $this->client->request('GET', $endpoint);
            $body = json_decode($response->getBody()->getContents(), true);

            \Log::info("Respons dari API:", $body);

            return response()->json($body);
        } catch (\Exception $e) {
            \Log::error("Gagal mengambil data dari API: {$e->getMessage()}");

            return response()->json(['error' => 'Unable to fetch data'], 500);
        }
    }

    public function store(Request $request)
    {
        $url = config('api.base_url'); // Mendapatkan URL API dari config

        // Mengirim data melalui POST request ke API
        $response = $this->client->request('POST', "{$url}/karyawan", [
            'json' => $request->all(), // Mengirimkan data form yang diterima
        ]);

        // Mengambil response untuk melihat apakah data berhasil ditambahkan
        $karyawan = json_decode($response->getBody()->getContents(), true);

        // Redirect atau menampilkan halaman lain
        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil ditambahkan');
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
        $url = config('api.base_url'); // Mendapatkan URL API dari config

        // Mengirimkan data melalui PUT request untuk mengupdate karyawan
        $response = $this->client->request('PUT', "{$url}/karyawan/{$id}", [
            'json' => $request->all(), // Mengirimkan data form yang diperbarui
        ]);

        // Mengambil response untuk melihat apakah data berhasil diperbarui
        $karyawan = json_decode($response->getBody()->getContents(), true);

        // Redirect ke halaman index setelah update berhasil
        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil diperbarui');
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
