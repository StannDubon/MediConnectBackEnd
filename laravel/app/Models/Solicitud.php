<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    protected $table ='solicitudes';

    protected $fillable = [
        'areas_doctores_id',
        'pacientes_id',
    ];
}
