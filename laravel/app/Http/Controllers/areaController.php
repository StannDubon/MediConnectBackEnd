<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Area;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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



    public function store(Request $request): JsonResponse
    {

        try {
            $validated = $request->validate([
                'nombre' => 'required',
            ]);

            $area = Area::create($validated);

            return response()->json([
                'message' => 'Área creada exitosamente',
                'area' => $area,
            ], 201);

        } catch (ValidationException $e) {
            $data = [
                'message' => 'Error al validar los datos',
                'errors' => $e->errors(),
                'status' => 422,
            ];
            return response()->json($data, 422);
        }
    }



}
