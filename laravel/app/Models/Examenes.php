<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Examenes extends Model
{
    protected $table = 'examenes';

    protected $fillable = [
        'paciente_id',
        'titulo',
        'descripcion',
        'fecha',
    ];

    // Relación con Paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }
}