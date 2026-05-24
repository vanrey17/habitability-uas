<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('monitoring_logs', function (Blueprint $table) {
        $table->id();

        // Hanya menyimpan nilai ADC mentah (0 - 4095) dari masing-masing sensor gas
        $table->integer('mq135_value')->nullable();
        $table->integer('mq8_value')->nullable();
        $table->integer('mq4_value')->nullable();
        $table->integer('mq9_value')->nullable();
        $table->float('temperature', 8, 2)->nullable();
        $table->float('humidity', 8, 2)->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_logs');
    }
};
