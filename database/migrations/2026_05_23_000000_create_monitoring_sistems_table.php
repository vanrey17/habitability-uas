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

            // MQ135 Sensor Data
            $table->float('mq135_ppm', 10, 2)->nullable();
            $table->integer('mq135_value')->nullable();

            // MQ8 Sensor Data
            $table->float('mq8_ppm', 10, 2)->nullable();
            $table->integer('mq8_value')->nullable();

            // MQ4 Sensor Data
            $table->float('mq4_ppm', 10, 2)->nullable();
            $table->integer('mq4_value')->nullable();

            // MQ9 Sensor Data
            $table->float('mq9_ppm', 10, 2)->nullable();
            $table->integer('mq9_value')->nullable();

            // DHT22 Sensor Data
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
        Schema::dropIfExists('monitoring_sistems');
    }
};
