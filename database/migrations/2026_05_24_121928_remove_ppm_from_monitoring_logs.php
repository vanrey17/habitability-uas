<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoring_logs', function (Blueprint $table) {
            $table->dropColumn([
                'mq135_ppm',
                'mq8_ppm',
                'mq4_ppm',
                'mq9_ppm'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('monitoring_logs', function (Blueprint $table) {
            $table->float('mq135_ppm', 10, 2)->nullable();
            $table->float('mq8_ppm', 10, 2)->nullable();
            $table->float('mq4_ppm', 10, 2)->nullable();
            $table->float('mq9_ppm', 10, 2)->nullable();
        });
    }
};