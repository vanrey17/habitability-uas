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
        Schema::create('monitoring_final', function (Blueprint $table) {
            $table->id();

            // relasi ke data mentah
            $table->foreignId('monitoring_log_id')
                  ->constrained('monitoring_logs')
                  ->onDelete('cascade');

            // data hasil olahan
            $table->float('mq135_ppm', 10, 2)->nullable();
            $table->float('mq8_ppm', 10, 2)->nullable();
            $table->float('mq4_ppm', 10, 2)->nullable();
            $table->float('mq9_ppm', 10, 2)->nullable();

            $table->string('status')->nullable(); // layak/tidak
            $table->float('air_quality_index')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_final');
    }
};
