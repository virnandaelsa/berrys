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
    ], [
        'username.required' => 'Username wajib diisi!',
        'password.required' => 'Password wajib diisi!',
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
        Log::info('Response body dari API login:', $responseBody);


        // Jika responsenya {"token": "xxx"}
        session(['token' => $responseBody['access_token']]);

        // Redirect ke halaman utama atau halaman lain setelah login berhasil
        return redirect('/dashboard')->with('success', 'Login berhasil');

    } catch (\GuzzleHttp\Exception\ClientException $e) {
        // Menangani kesalahan dan mengarahkan kembali dengan pesan kesalahan
        Log::error('Login failed', [
            'username' => $request->username,
            'error' => $e->getMessage()
        ]);
        return back()->with('error', 'Login Gagal Username & Password Salah');
    }
}

    public function logout(Request $request)
    {
        Log::info('Logout attempt for User');

        if (session()->has('token')) {
            try {
                $client = new \GuzzleHttp\Client();

                $response = $client->request('POST', config('api.base_url') . '/logout', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . session('token'),
                    ],
                ]);

                session()->forget('token');
                session()->invalidate();
                session()->regenerateToken();

                Log::info('Logout successful for User');

                return redirect('/')->with('success', 'Logout berhasil');
            } catch (\Exception $e) {
                Log::error('Logout failed', ['error' => $e->getMessage()]);
                return redirect()->back()->with('error', 'Logout gagal');
            }
        } else {
            session()->forget('token');
            session()->invalidate();
            session()->regenerateToken();

            Log::info('Logout successful without token');

            return redirect('/')->with('success', 'Logout berhasil');
        }
    }
}
