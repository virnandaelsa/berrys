<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function showLoginForm()
    {
        Log::info('Menampilkan halaman login');
        return view('content.authentications.login');
    }

    public function login(Request $request)
{
    // Validasi input
    $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);

    $url = config('api.base_url') . '/login';

    Log::info('Mencoba login', [
        'username' => $request->username,
        'endpoint' => $url
    ]);

    try {
        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'username' => $request->username,
                'password' => $request->password,
            ])
        ]);

        $responseBody = json_decode($response->getBody(), true);

        session(['token' => $responseBody['access_token']]);

        // Redirect ke halaman utama atau halaman lain setelah login berhasil
        return redirect('/dashboard');

    } catch (\GuzzleHttp\Exception\ClientException $e) {
        // Menangani kesalahan dan mengarahkan kembali dengan pesan kesalahan
        Log::error('Login failed', [
            'username' => $request->username,
            'error' => $e->getMessage()
        ]);
        return back()->with('loginError', 'Login Gagal');
    }
}

}
