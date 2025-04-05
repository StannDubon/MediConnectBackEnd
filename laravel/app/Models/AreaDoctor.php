<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaDoctor extends Model
{
    protected $table ='areas_doctores';

    protected $fillable = [
        'areas_id',
        'doctores_id',
    ];
}
