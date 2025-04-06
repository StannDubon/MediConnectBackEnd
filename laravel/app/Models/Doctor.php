<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $table ='doctores';

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'password',
        'clinica_diaria',
        'imagen'
    ];
}
