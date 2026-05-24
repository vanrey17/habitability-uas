<?php

namespace App\Http\Controllers;

use App\Models\MonitoringSistem;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB; // WAJIB DITAMBAHKAN untuk menyimpan ke tabel monitoring_final

class MonitoringSistemController extends Controller
{
    /**
     * Display a listing of the resource (Latest 100 integrated logs for testing).
     */
    public function index(Request $request)
    {
        // Menggunakan JOIN agar saat melakukan GET /api/monitoring, data mentah dan matang bersatu
        $data = DB::table('monitoring_logs' )
            ->join('monitoring_final', 'monitoring_logs.id', '=', 'monitoring_final.monitoring_log_id')
            ->select('monitoring_logs.*', 'monitoring_final.mq135_ppm', 'monitoring_final.mq8_ppm', 'monitoring_final.mq4_ppm', 'monitoring_final.mq9_ppm', 'monitoring_final.air_quality_index', 'monitoring_final.status')
            ->latest('monitoring_logs.id')
            ->take(100)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $data
        ], 200);
    }

    /**
     * Store a newly created resource in storage (Receive logs from ESP32/Postman).
     */
    public function store(Request $request)
    {
        // 1. VALIDASI: Pastikan format data mentah sesuai dengan struktur tabel monitoring_logs yang baru
        $validator = Validator::make($request->all(), [
            'mq135_value' => 'nullable|integer',
            'mq8_value'   => 'nullable|integer',
            'mq4_value'   => 'nullable|integer',
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

        // 2. SIMPAN TAHAP 1: Masukkan data fisik mentah dari ESP32 ke tabel 'monitoring_logs'
        $logMentah = MonitoringSistem::create([
            'mq135_value' => $request->mq135_value,
            'mq8_value'   => $request->mq8_value,
            'mq4_value'   => $request->mq4_value,
            'mq9_value'   => $request->mq9_value,
            'temperature' => $request->temperature,
            'humidity'    => $request->humidity,
        ]);

        // Default value jika mesin Python FastAPI sedang mati / error
        $ppm135 = 0; $ppm8 = 0; $ppm4 = 0; $ppm9 = 0;
        $skor_fuzzy = 0;
        $status_fuzzy = 'Python Offline';

        // 3. ESTAFET KE PYTHON: Kirim seluruh paket data mentah ke FastAPI untuk dikonversi & dihitung Fuzzy-nya
        try {
            $responseDariPython = Http::post('http://127.0.0.1:8000/hitung-fuzzy', [
                'mq135_value' => $request->mq135_value ?? 0,
                'mq8_value'   => $request->mq8_value ?? 0,
                'mq4_value'   => $request->mq4_value ?? 0,
                'mq9_value'   => $request->mq9_value ?? 0,
                'temperature' => $request->temperature ?? 0,
                'humidity'    => $request->humidity ?? 0,
            ]);

            if ($responseDariPython->successful()) {
                $hasilFuzzy = $responseDariPython->json();
                $ppm135       = $hasilFuzzy['mq135_ppm'];
                $ppm8         = $hasilFuzzy['mq8_ppm'];
                $ppm4         = $hasilFuzzy['mq4_ppm'];
                $ppm9         = $hasilFuzzy['mq9_ppm'];
                $skor_fuzzy   = $hasilFuzzy['skor'];
                $status_fuzzy = $hasilFuzzy['status'];
            }
        } catch (\Exception $e) {
            $status_fuzzy = 'Python Offline';
        }

        // 4. SIMPAN TAHAP 2: Ikat id $logMentah tadi sebagai foreign key ke tabel 'monitoring_final'
        DB::table('monitoring_final')->insert([
            'monitoring_log_id' => $logMentah->id, // Mengunci relasi antar tabel!
            'mq135_ppm'         => $ppm135,
            'mq8_ppm'           => $ppm8,
            'mq4_ppm'           => $ppm4,
            'mq9_ppm'           => $ppm9,
            'status'            => $status_fuzzy,
            'air_quality_index' => $skor_fuzzy,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // 5. RESPONS: Mengembalikan kepastian status sukses gabungan dalam format JSON
        return response()->json([
            'success' => true,
            'message' => 'Data sukses dipecah dan disimpan ke sistem database relasional S-HABITAT!',
            'log_id'  => $logMentah->id
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $data = DB::table('monitoring_logs')
            ->join('monitoring_final', 'monitoring_logs.id', '=', 'monitoring_final.monitoring_log_id')
            ->select('monitoring_logs.*', 'monitoring_final.mq135_ppm', 'monitoring_final.mq8_ppm', 'monitoring_final.mq4_ppm', 'monitoring_final.mq9_ppm', 'monitoring_final.air_quality_index', 'monitoring_final.status')
            ->where('monitoring_logs.id', $id)
            ->first();

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $data
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
            'mq135_value' => 'nullable|integer',
            'mq8_value'   => 'nullable|integer',
            'mq4_value'   => 'nullable|integer',
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
            'mq135_value', 'mq8_value', 'mq4_value', 'mq9_value', 'temperature', 'humidity'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Data mentah log berhasil diperbarui.'
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

        // Karena migrations menggunakan ->onDelete('cascade'), menghapus log mentah otomatis menghapus data final-nya!
        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data monitoring berhasil dihapus dari database log dan final.'
        ], 200);
    }
}
