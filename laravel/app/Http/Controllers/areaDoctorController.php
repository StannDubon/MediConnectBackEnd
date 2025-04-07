<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AreaDoctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class areaDoctorController extends Controller
{
    public function index(){
        $doctores = AreaDoctor::all();
        if($doctores->isEmpty()){
            $data=[
                'message' => 'No se encontraron doctores existentes',
                'status' => 200,
            ];
            return response()->json($data, 200);
        }

        $data=[
            'doctores' => $doctores,
            'status' => 200,
        ];
        return response()->json($data, 200);
    }
}
