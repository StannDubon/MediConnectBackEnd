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
        Schema::create('areas_doctores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('areas_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctores_id')->constrained('doctores')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('areas_doctores');
    }
};
