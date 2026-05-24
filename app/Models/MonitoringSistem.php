<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoringSistem extends Model
{
    use HasFactory;

    // Explicitly define the table name to match the migration
    protected $table = 'monitoring_logs';

    // Mass assignable attributes
    protected $fillable = [
        'mq135_ppm',
        'mq135_value',
        'mq8_ppm',
        'mq8_value',
        'mq4_ppm',
        'mq4_value',
        'mq9_ppm',
        'mq9_value',
        'temperature',
        'humidity',
        'habitability_score',
        'status'
    ];
}
