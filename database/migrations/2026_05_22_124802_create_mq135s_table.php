<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mq135s', function (Blueprint $table) {
            $table->id();
            $table->float('ppm', 10, 2); // <--- PASTIKAN BARIS INI ADA
            $table->integer('value')->nullable(); // <--- PASTIKAN BARIS INI ADA
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mq135s');
    }
};
