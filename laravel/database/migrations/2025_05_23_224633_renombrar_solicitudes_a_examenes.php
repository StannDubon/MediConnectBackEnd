<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar la tabla vieja si existe
        Schema::dropIfExists('solicitudes');

        // Crear la nueva tabla 'examenes'
        Schema::create('examenes', function (Blueprint $table) {
            $table->id(); // ID principal
            $table->date('fecha'); // Fecha del examen
            $table->string('titulo'); // Título del examen
            $table->text('descripcion')->nullable(); // Descripción opcional
            $table->unsignedBigInteger('paciente_id'); // Relación con paciente

            // Clave foránea
            $table->foreign('paciente_id')->references('id')->on('pacientes')->onDelete('cascade');

            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down(): void
    {
        // Elimina la tabla 'examenes' si se revierte la migración
        Schema::dropIfExists('examenes');
    }
};
