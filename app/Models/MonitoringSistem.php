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
        'mq135_value',
        'mq8_value',
        'mq4_value',
        'mq9_value',
        'temperature',
        'humidity',
        'habitability_score',
        'status'
    ];
}
