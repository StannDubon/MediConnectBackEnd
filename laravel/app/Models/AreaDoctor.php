<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaDoctor extends Model
{
    protected $table ='areas_doctores';

    protected $fillable = [
        'area_id',
        'doctor_id',
    ];

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'areas_doctores_id');
    }
}
