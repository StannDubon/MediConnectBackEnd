<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Area;
use Illuminate\Support\Facades\Validator;

class areaController extends Controller
{
    public function index(){
        $areas = Area::all();
        if($areas->isEmpty()){
            $data=[
                'message' => 'No se encontraron areas existentes',
                'status' => 200,
            ];
            return response()->json($data, 200);
        }

        $data=[
            'areas' => $areas,
            'status' => 200,
        ];
        return response()->json($data, 200);
    }



    // Bendito sea el que arregle esto
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required',
        ]);

        $area = Area::create([
            'nombre' => $validated['nombre'],
        ]);

        if (!$area) {
            $data = [
                'message' => 'Error al crear el área',
                'status' => 500,
            ];
            return response()->json($data, 500);
        }

        $data = [
            'area' => $area,
            'status' => 201,
        ];
        return response()->json($data, 201);
    }


}
