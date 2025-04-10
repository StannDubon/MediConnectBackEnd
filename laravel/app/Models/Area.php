<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table ='areas';

    protected $fillable = [
        'nombre',
    ];

    public function doctores()
    {
        return $this->belongsToMany(Doctor::class, 'areas_doctores', 'area_id', 'doctor_id');
    }
}
