<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mq135 extends Model
{
    use HasFactory;

    // Menegaskan nama tabel di database agar tidak terjadi salah tebak oleh sistem
    protected $table = 'mq135s';

    // Kolom yang diizinkan untuk diisi massal lewat Controller
    protected $fillable = [
        'ppm',
        'value'
    ];
}
