<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

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

    // Mendapatkan URL API dari config
    $url = config('api.base_url');

    try {
        // Ambil data absensi dari API
        $response = $this->client->request('GET', "{$url}/absensi");

        // Decode data JSON yang diterima
        $karyawanData = json_decode($response->getBody()->getContents(), true);

        // Pastikan respons JSON memiliki key 'data'
        if (!isset($karyawanData['data'])) {
            throw new \Exception('Respons API tidak memiliki key "data".');
        }

        $karyawanData = $karyawanData['data'];
    } catch (\Exception $e) {
        // Tampilkan pesan error jika API gagal diakses
        return view('content.absensi.view', [
            'karyawanData' => [],
            'tanggal' => $tanggal, // Pastikan $tanggal tetap dikirim
        ])->with('message', 'Gagal mengambil data absensi. Silakan coba lagi.');
    }

    // Filter absensi berdasarkan tanggal yang diberikan
    $tanggal = \Carbon\Carbon::parse($tanggal)->format('Y-m-d');
    $karyawanData = array_values(array_filter($karyawanData, function ($absen) use ($tanggal) {
        return isset($absen['tanggal']) &&
               \Carbon\Carbon::parse($absen['tanggal'])->isSameDay(\Carbon\Carbon::parse($tanggal));
    }));

    // Kelompokkan absensi berdasarkan ID karyawan
    $groupedData = [];
    foreach ($karyawanData as $absen) {
        $idKaryawan = $absen['karyawan']['id'] ?? null;
        if (!$idKaryawan) {
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

    // Kirim data ke view
    return view('content.absensi.view', [
        'karyawanData' => $groupedData,
        'tanggal' => $tanggal,
    ]);
}
}
