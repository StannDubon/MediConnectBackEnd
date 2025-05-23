<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Solicitud extends Model
{
    protected $table ='solicitudes';

    protected $fillable = [
        'areas_doctores_id',
        'pacientes_id',
        'motivo',
        'notas',
        'fila'
    ];

    public function areaDoctor()
    {
        return $this->belongsTo(AreaDoctor::class, 'areas_doctores_id');
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'pacientes_id');
    }
}