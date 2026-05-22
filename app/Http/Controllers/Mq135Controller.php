<?php

namespace App\Http\Controllers;

use App\Models\mq135; // Memanggil model mq135 (sesuai huruf kecil nama filemu)
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Mq135Controller extends Controller
{
    // Mengambil 100 data log sensor terbaru
    public function index()
    {
        // Menggunakan select() untuk mengambil kolom tertentu saja
        $data = mq135::select('id', 'ppm', 'value')
                    ->latest()
                    ->take(100)
                    ->get();

        return response()->json([
            'data'    => $data
        ], 200);
    }

    // Menerima POST request dari Postman / ESP32 untuk disimpan ke database
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ppm'   => 'required|numeric',
            'value' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 400);
        }

        $mq135 = mq135::create([
            'ppm'   => $request->ppm,
            'value' => $request->value,
        ]);

        // Mengubah format return agar hanya menampilkan objek "data" saja
        return response()->json([
            'data' => $mq135
        ], 201);
    }

    // Melihat detail 1 data berdasarkan ID (api/mq135/{id})
    public function show($id)
    {
        // Mengambil kolom spesifik saja berdasarkan ID
        $data = mq135::select('id', 'ppm', 'value')->find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        }

        // Mengembalikan data bersih tanpa created_at & updated_at
        return response()->json([
            'data' => $data
        ], 200);
    }

    // Mengubah data log berdasarkan ID
    public function update(Request $request, $id)
    {

    }

    // Menghapus data log berdasarkan ID
    public function destroy($id)
    {
        $data = mq135::find($id);
        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }
        $data->delete();
        return response()->json(['success' => true, 'message' => 'Data sensor berhasil dihapus.'], 200);
    }
}
