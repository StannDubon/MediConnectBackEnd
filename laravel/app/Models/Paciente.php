<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Paciente extends Model
{
    use HasApiTokens;
    protected $table ='pacientes';

    protected $fillable = [
        'nombre',
        'apellido'
    ];

    public function examenes()
    {
        return $this->hasMany(Examenes::class, 'pacientes_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'paciente_id');
    }
}
