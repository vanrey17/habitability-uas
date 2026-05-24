<?php

namespace App\Http\Controllers;

use App\Models\MonitoringSistem;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class MonitoringSistemController extends Controller
{
    /**
     * Display a listing of the resource (Latest 100 monitoring logs).
     */
    public function index(Request $request)
    {
        $data = MonitoringSistem::latest()
            ->take(100)
            ->get();

        return response()->json([
            'data'    => $data
        ], 200);
    }

    /**
     * Store a newly created resource in storage (Receive logs from Postman/ESP32).
     */
    public function store(Request $request)
    {
        // 1. PENGAMAN: Cek Token Unik ala Google AI Studio (Bisa ditaruh di Header atau Body Request)
        // $TOKEN_RAHASIA = "shabitat_key_uas_elvan_2026";
        // $tokenDariIoT = $request->header('Authorization') ?? $request->token;

        // if ($tokenDariIoT !== $TOKEN_RAHASIA) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Akses Ditolak! Token API salah atau tidak disertakan.'
        //     ], 401);
        // }

        // 2. VALIDASI: Struktur nullable sesuai request kamu
        $validator = Validator::make($request->all(), [
            'mq135_ppm'   => 'nullable|numeric',
            'mq135_value' => 'nullable|integer',
            'mq8_ppm'     => 'nullable|numeric',
            'mq8_value'   => 'nullable|integer',
            'mq4_ppm'     => 'nullable|numeric',
            'mq4_value'   => 'nullable|integer',
            'mq9_ppm'     => 'nullable|numeric',
            'mq9_value'   => 'nullable|integer',
            'temperature' => 'nullable|numeric',
            'humidity'    => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 400);
        }

        // Default value untuk hasil hitung Fuzzy (jika mesin Python belum aktif/mengalami error)
        $skor_fuzzy = $request->habitability_score ?? 0;
        $status_fuzzy = $request->status ?? 'Pending';

        // Hanya menembak Python jika di Postman TIDAK mengirimkan skor & status secara manual
        if (!$request->has('habitability_score') && !$request->has('status')) {
            try {
                // 3. ESTAFET KE PYTHON: Kirim parameter utama ke FastAPI jika testing manual Postman kosong
                $responseDariPython = Http::post('http://127.0.0.1:8000/hitung-fuzzy', [
                    'suhu' => $request->temperature ?? 0,
                    'co2'  => $request->mq135_ppm ?? 0
                ]);

                if ($responseDariPython->successful()) {
                    $hasilFuzzy = $responseDariPython->json();
                    $skor_fuzzy = $hasilFuzzy['skor'];
                    $status_fuzzy = $hasilFuzzy['status'];
                }
            } catch (\Exception $e) {
                // Jika Python FastAPI mati, status diatur menjadi 'Python Offline'
                $status_fuzzy = 'Python Offline';
            }
        }

        // 4. PROSES SIMPAN: Menyimpan semua data sensor + hasil kalkulasi Fuzzy ke tabel 'monitoring_logs'
        $monitoring = MonitoringSistem::create([
            'mq135_ppm'          => $request->mq135_ppm,
            'mq135_value'        => $request->mq135_value,
            'mq8_ppm'            => $request->mq8_ppm,
            'mq8_value'          => $request->mq8_value,
            'mq4_ppm'            => $request->mq4_ppm,
            'mq4_value'          => $request->mq4_value,
            'mq9_ppm'            => $request->mq9_ppm,
            'mq9_value'          => $request->mq9_value,
            'temperature'        => $request->temperature,
            'humidity'           => $request->humidity,
            'habitability_score' => $skor_fuzzy,  // Menyimpan skor hasil olahan Python
            'status'             => $status_fuzzy, // Menyimpan status hasil olahan Python
        ]);

        // 5. RESPONS: Mengembalikan data yang berhasil disimpan dalam format JSON
        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan dan diolah!',
            'data'    => $monitoring
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $data = MonitoringSistem::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'data' => $data
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = MonitoringSistem::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'mq135_ppm'   => 'nullable|numeric',
            'mq135_value' => 'nullable|integer',
            'mq8_ppm'     => 'nullable|numeric',
            'mq8_value'   => 'nullable|integer',
            'mq4_ppm'     => 'nullable|numeric',
            'mq4_value'   => 'nullable|integer',
            'mq9_ppm'     => 'nullable|numeric',
            'mq9_value'   => 'nullable|integer',
            'temperature' => 'nullable|numeric',
            'humidity'    => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 400);
        }

        $data->update($request->only([
            'mq135_ppm', 'mq135_value',
            'mq8_ppm', 'mq8_value',
            'mq4_ppm', 'mq4_value',
            'mq9_ppm', 'mq9_value',
            'temperature', 'humidity'
        ]));

        return response()->json([
            'data' => $data
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $data = MonitoringSistem::find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data monitoring berhasil dihapus.'
        ], 200);
    }
}
