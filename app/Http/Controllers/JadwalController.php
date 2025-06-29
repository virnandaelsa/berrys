<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class JadwalController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function index(Request $request)
{
    $url = config('api.base_url');
    $token = session('token'); // Ambil token dari session

    // Ambil tanggal dari request atau set default ke minggu ini
    $tanggal_mulai = $request->query('tanggal_mulai')
        ? Carbon::parse($request->query('tanggal_mulai'))->startOfWeek(Carbon::MONDAY)
        : now()->startOfWeek(Carbon::MONDAY);

    $tanggal_akhir = $tanggal_mulai->copy()->addDays(6);

    try {
        // Ambil data jadwal dari API
        $response = $this->client->request('GET', "{$url}/jadwal", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token, // Tambahkan header Authorization
                ],
            'query' => [
                'tanggal_mulai' => $tanggal_mulai->toDateString(),
                'tanggal_akhir' => $tanggal_akhir->toDateString()
            ]
        ]);

        $jadwalData = json_decode($response->getBody()->getContents(), true)['data'] ?? [];

        // Log data mentah dari API
        \Log::info('Data mentah dari API:', $jadwalData);

        // Validasi data API
        $validJadwalData = collect($jadwalData)->filter(function ($item) {
            $isValid = isset($item['tempat'], $item['shift'], $item['hari'], $item['nama_karyawan']);
            if (!$isValid) {
                \Log::warning('Data tidak valid ditemukan:', $item);
            }
            return $isValid;
        });

        // Log data yang valid setelah difilter
        \Log::info('Data valid setelah difilter:', $validJadwalData->toArray());
        $jadwalList = [
        'Pahing' => [1, 2],
        'Bandar' => ['Fulltime'],
        'Balowerti' => ['Fulltime'],
        'Dlopo' => ['Fulltime'],
        'Mojoroto' => ['Fulltime'],
        'Toko' => ['Fulltime'],
        'Pesantren' => ['Fulltime'],
        'Ngronggo' => ['Fulltime'],
        'Kurir' => [1, 2],
        'Produksi' => ['Fulltime'],
    ];

        $groupedJadwal = $validJadwalData
            ->groupBy(['tempat', 'shift'])
            ->map(function ($shiftGroups, $tempat) {
                \Log::info("Mengelompokkan data untuk tempat: $tempat", $shiftGroups->toArray());

                return collect($shiftGroups)->map(function ($jadwal, $shift) use ($tempat) {
                    // Pastikan data adalah koleksi
                    if (!is_array($jadwal) && !($jadwal instanceof \Illuminate\Support\Collection)) {
                        \Log::error("Data jadwal bukan array atau koleksi untuk tempat: $tempat, shift: $shift", [
                            'data' => $jadwal
                        ]);
                        return [];
                    }

                    \Log::info("Mengelompokkan data untuk shift: $shift di tempat: $tempat", collect($jadwal)->toArray());

                    // Inisialisasi array untuk hari
                    $hariGrouped = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                    $hariToKaryawan = [];

                    foreach ($hariGrouped as $hari) {
                        // Ambil nama karyawan untuk hari tertentu
                        $karyawanHari = collect($jadwal)
                            ->filter(function ($item) use ($hari) {
                                return isset($item['hari']) && $item['hari'] === $hari; // Filter berdasarkan hari
                            })
                            ->pluck('nama_karyawan') // Ambil nama karyawan
                            ->unique() // Hilangkan duplikasi nama
                            ->implode(', '); // Gabungkan nama dengan koma

                        $hariToKaryawan[$hari] = $karyawanHari;

                        // Log hasil untuk debugging
                        \Log::info("Nama karyawan untuk $hari di tempat: $tempat, shift: $shift", [
                            'nama_karyawan' => $karyawanHari,
                        ]);
                    }


                    return [
                        'tempat' => $tempat,
                        'shift' => $shift,
                        'hari' => $hariToKaryawan, // Nama karyawan per hari
                    ];
                })->values()->toArray();
            })->flatten(1)->toArray();

        \Log::info('Hasil akhir setelah menggabungkan nama karyawan:', $groupedJadwal);

    } catch (\Exception $e) {
        // Log error yang terjadi selama proses
        \Log::error("Gagal mengambil data jadwal dari API: " . $e->getMessage(), [
            'tanggal_mulai' => $tanggal_mulai->toDateString(),
            'tanggal_akhir' => $tanggal_akhir->toDateString(),
        ]);
        $groupedJadwal = [];
    }

    return view('content.jadwal.view', compact('groupedJadwal', 'tanggal_mulai', 'tanggal_akhir', 'jadwalList'));
}

    public function create()
{
    $url = config('api.base_url');
    $token = session('token'); // Ambil token dari session

    // Pastikan tanggal mulai selalu Senin
    $tanggal_mulai = now();
    if ($tanggal_mulai->dayOfWeek != 1) { // 1 = Senin
        $tanggal_mulai = $tanggal_mulai->next(Carbon::MONDAY);
    }

    // Tanggal akhir otomatis menjadi Minggu setelahnya
    $tanggal_akhir = $tanggal_mulai->copy()->addDays(6);

    // Tanggal per hari
    $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    $tanggalPerHari = [];
    foreach ($hariList as $idx => $hari) {
        $tanggalPerHari[$hari] = $tanggal_mulai->copy()->addDays($idx)->toDateString();
    }

    // Daftar tempat statis
    $jadwalList = [
        'Pahing' => [1, 2],
        'Bandar' => ['Fulltime'],
        'Balowerti' => ['Fulltime'],
        'Dlopo' => ['Fulltime'],
        'Mojoroto' => ['Fulltime'],
        'Toko' => ['Fulltime'],
        'Pesantren' => ['Fulltime'],
        'Ngronggo' => ['Fulltime'],
        'Kurir' => [1, 2],
        'Produksi' => ['Fulltime'],
    ];

    try {
        // Ambil semua data karyawan
        $response = $this->client->request('GET', "{$url}/karyawan", [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
        $data = json_decode($response->getBody()->getContents(), true);

        // Filter karyawan dengan status "Aktif"
        $karyawanList = collect($data['data'] ?? [])->filter(function ($karyawan) {
            return $karyawan['status'] === 'Aktif';
        })->values()->toArray();

        $cutiByKaryawan = [];
    } catch (\Exception $e) {
        $karyawanList = [];
    }

    try {
        $cutiResponse = $this->client->request('GET', "{$url}/cuti", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
            'query' => [
                'tanggal_mulai' => $tanggal_mulai->toDateString(),
                'tanggal_akhir' => $tanggal_akhir->toDateString(),
            ]
        ]);

        $cutiData = json_decode($cutiResponse->getBody()->getContents(), true)['data'] ?? [];

        foreach ($cutiData as $cuti) {
            if (trim($cuti['status']) === 'diterima') {
                $id = $cuti['id_karyawan'];
                $tanggal = date('Y-m-d', strtotime($cuti['tanggal_cuti']));
                $cutiByKaryawan[$id][] = $tanggal;
            }
        }

    } catch (\Exception $e) {
        $cutiByKaryawan = [];
    }

    return view('content.jadwal.create', compact(
        'jadwalList',
        'karyawanList',
        'tanggal_mulai',
        'tanggal_akhir',
        'cutiByKaryawan',
        'tanggalPerHari'
    ));
}

    public function store(Request $request)
    {
        $url = config('api.base_url');
        $token = session('token'); // Ambil token dari session

        // Ambil tanggal awal dan akhir
        $tanggalMulai = Carbon::parse($request->tanggal_mulai);
        $tanggalAkhir = Carbon::parse($request->tanggal_akhir);

        // Persiapkan data jadwal yang akan dikirim ke API
        $jadwalData = [];

        // Daftar hari dalam satu minggu
        $daftarHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        foreach ($request->jadwal as $tempat => $shifts) {
            foreach ($shifts as $shift => $hariList) {
                foreach ($hariList as $hari => $data) {
                    if (!empty($data['id_karyawan'])) {
                        // Cari indeks hari dalam daftar
                        $hariIndex = array_search($hari, $daftarHari);

                        if ($hariIndex === false) {
                            return back()->with('error', "Hari '{$hari}' tidak valid.");
                        }

                        // Hitung tanggal berdasarkan tanggal_mulai
                        $tanggalJadwal = $tanggalMulai->copy()->addDays($hariIndex)->toDateString();

                        // Simpan data jadwal yang valid
                        $jadwalData[] = [
                            'tempat' => $tempat,
                            'shift' => (string) $shift,
                            'hari' => $hari,
                            'date' => $tanggalJadwal,
                            'id_karyawan' => $data['id_karyawan'],
                        ];
                    }
                }
            }
        }

    // Kirim data jadwal ke API
        try {
            $response = $this->client->request('POST', "{$url}/jadwal", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token, // Tambahkan header Authorization
                ],
                'json' => ['jadwal' => $jadwalData],
            ]);
            return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan jadwal: ' . $e->getMessage());
        }
    }

    public function edit(Request $request)
{
    $url = config('api.base_url');
    $token = session('token'); // Ambil token dari session
    \Log::info("Memulai fungsi edit dengan URL: {$url}");

    // Ambil tanggal dari request atau default ke minggu ini
    $tanggal_mulai = $request->query('tanggal_mulai')
        ? Carbon::parse($request->query('tanggal_mulai'))->startOfWeek(Carbon::MONDAY)
        : now()->startOfWeek(Carbon::MONDAY);

    \Log::info("Tanggal mulai: {$tanggal_mulai->toDateString()}");

    $tanggal_akhir = $tanggal_mulai->copy()->addDays(6);
    \Log::info("Tanggal akhir: {$tanggal_akhir->toDateString()}");

    try {
        // Ambil data jadwal berdasarkan rentang tanggal
        $response = $this->client->request('GET', "{$url}/jadwal", [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token, // Tambahkan header Authorization
            ],
            'query' => [
                'tanggal_mulai' => $tanggal_mulai->toDateString(),
                'tanggal_akhir' => $tanggal_akhir->toDateString()
            ]
        ]);
        $jadwalData = json_decode($response->getBody()->getContents(), true)['data'] ?? [];
        \Log::info("Berhasil mengambil data jadwal: " . json_encode($jadwalData));
    } catch (\Exception $e) {
        \Log::error("Gagal mengambil data jadwal: " . $e->getMessage());
        $jadwalData = [];
    }

    try {
        // Ambil semua data karyawan
        $response = $this->client->request('GET', "{$url}/karyawan", [
            'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token, // Tambahkan header Authorization
                ],
        ]);
        $data = json_decode($response->getBody()->getContents(), true);

        // Filter karyawan dengan status "Aktif"
        $karyawanList = collect($data['data'] ?? [])->filter(function ($karyawan) {
            return $karyawan['status'] === 'Aktif';
        })->values()->toArray();

        \Log::info("Karyawan Aktif: " . json_encode($karyawanList));

    } catch (\Exception $e) {
        \Log::error("Gagal mengambil data karyawan: " . $e->getMessage());
        $karyawanList = [];
    }

    // Daftar tempat statis
    $jadwalList = [
        'Pahing' => [1, 2],
        'Bandar' => ['Fulltime'],
        'Balowerti' => ['Fulltime'],
        'Dlopo' => ['Fulltime'],
        'Mojoroto' => ['Fulltime'],
        'Toko' => ['Fulltime'],
        'Pesantren' => ['Fulltime'],
        'Ngronggo' => ['Fulltime'],
        'Kurir' => [1, 2],
        'Produksi' => ['Fulltime'],
    ];

    \Log::info("Daftar tempat: " . json_encode($jadwalList));

    return view('content.jadwal.edit', compact('jadwalData', 'jadwalList', 'karyawanList', 'tanggal_mulai', 'tanggal_akhir'));
}

public function update(Request $request)
{
    \Log::info("Mulai proses update jadwal.");

    $validator = Validator::make($request->all(), [
        'tanggal_mulai' => 'required|date',
        'jadwal' => 'required|array',
    ]);
    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $tanggalMulai = \Carbon\Carbon::parse($request->tanggal_mulai)->startOfWeek(\Carbon\Carbon::MONDAY);
    $daftarHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    // Kumpulkan data perubahan untuk dikirim ke API
    $perubahanJadwal = [];

    foreach ($request->jadwal as $tempat => $shifts) {
    foreach ($shifts as $shift => $hariList) {
        foreach ($hariList as $hari => $data) {
            $hariIndex = array_search($hari, $daftarHari);
            if ($hariIndex === false) continue;
            $tanggalJadwal = $tanggalMulai->copy()->addDays($hariIndex)->toDateString();

            // PRODUKSI: delete & insert
            if ($tempat === 'Produksi' && isset($data['id_karyawan_lama']) && is_array($data['id_karyawan_lama'])) {
                $lamaArr = array_filter($data['id_karyawan_lama']);
                $baruArr = array_filter($data['id_karyawan_baru'] ?? []);

                foreach ($lamaArr as $idLama) {
                    if (!in_array($idLama, $baruArr)) {
                        $perubahanJadwal[] = [
                            'action' => 'delete',
                            'tempat' => $tempat,
                            'shift' => (string)$shift,
                            'hari' => $hari,
                            'date' => $tanggalJadwal,
                            'id_karyawan' => $idLama,
                        ];
                    }
                }
                foreach ($baruArr as $idBaru) {
                    if (!in_array($idBaru, $lamaArr)) {
                        $perubahanJadwal[] = [
                            'action' => 'insert',
                            'tempat' => $tempat,
                            'shift' => (string)$shift,
                            'hari' => $hari,
                            'date' => $tanggalJadwal,
                            'id_karyawan' => $idBaru,
                        ];
                    }
                }
            }
            // KURIR/LAINNYA: update (single)
            elseif (isset($data['id_karyawan_lama'])) {
                $idLama = $data['id_karyawan_lama'];
                $idBaru = $data['id_karyawan_baru'] ?? null;
                if ($idBaru && $idBaru != $idLama) {
                    $perubahanJadwal[] = [
                        'action' => 'update',
                        'tempat' => $tempat,
                        'shift' => (string)$shift,
                        'hari' => $hari,
                        'date' => $tanggalJadwal,
                        'id_karyawan_lama' => $idLama,
                        'id_karyawan_baru' => $idBaru,
                    ];
                }
            }
        }
    }
}

    \Log::info("Data perubahan jadwal yang akan dikirim ke API: " . json_encode($perubahanJadwal));

    // Kirim ke API (misal pakai Guzzle)
    try {
        $client = new \GuzzleHttp\Client();
        $url = config('api.base_url') . '/jadwal/update'; // pastikan sesuai endpoint API kamu
        $token = session('token');

        $response = $client->request('PUT', $url, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
            'json' => [
                'tanggal_mulai' => $tanggalMulai->toDateString(),
                'jadwal' => $perubahanJadwal,
            ],
        ]);

        $status = $response->getStatusCode();
        $body = json_decode($response->getBody(), true);

        \Log::info("Respon API update jadwal: " . json_encode($body));

        if ($status == 200) {
            return redirect()->route('jadwal.index')->with('success', $body['message'] ?? 'Jadwal berhasil diupdate melalui API.');
        } else {
            return back()->with('error', 'Gagal update jadwal via API.');
        }
    } catch (\Exception $e) {
        \Log::error("Gagal update jadwal via API: " . $e->getMessage());
        return back()->with('error', 'Gagal update jadwal via API: ' . $e->getMessage());
    }
}

    public function show($id)
    {
        $url = config('api.base_url');
        $token = session('token'); // Ambil token dari session

        try {
            $response = $this->client->request('GET', "{$url}/jadwal", [
                'query' => [
                    'tanggal_mulai' => $tanggal_mulai->toDateString(),
                    'tanggal_akhir' => $tanggal_akhir->toDateString()
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token, // Tambahkan header Authorization
                ],
            ]);
            $jadwalData = json_decode($response->getBody()->getContents(), true)['data'] ?? [];
            dd($jadwalData);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengambil data jadwal: ' . $e->getMessage());
        }

        return view('content.jadwal.show', compact('jadwalData'));
    }

    public function destroy($id)
    {
        $url = config('api.base_url');

        $this->client->request('DELETE', "{$url}/jadwal/{$id}");

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil dihapus');
    }
}
