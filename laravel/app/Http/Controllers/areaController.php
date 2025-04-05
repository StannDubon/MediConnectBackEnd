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

    public function show($id)
    {
        $area = Area::find($id);
        if (!$area) {
            return response()->json([
                'message' => 'Área no encontrada',
                'status' => 404,
            ], 404);
        }
        return response()->json([
            'area' => $area,
            'status' => 200,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {

        try {
            $validated = $request->validate([
                'nombre' => 'required|min:2|max:255|unique:areas,nombre',
            ]);

            $area = Area::create($validated);

            return response()->json([
                'message' => 'Área creada exitosamente',
                'area' => $area,
                'status' => 200,
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

    public function destroy($id){
        $area = Area::find($id);
        if(!$area){
            return response()->json([
                'message' => 'Área no encontrada',
                'status' => 404,
            ], 404);
        }

        $area->delete();
        return response()->json([
            'message' => 'Área eliminada',
            'status' => 200,
        ], 200);
    }

    public function update(Request $request, $id){
        $area = Area::find($id);
        if(!$area){
            return response()->json([
                'message' => 'Área no encontrada',
                'status' => 404,
            ], 404);
        }

        try {
            $validated = $request->validate([
                'nombre' => 'required|min:2|max:255|unique:areas,nombre',
            ]);

            $area->nombre = $request->nombre;
            $area->save();

            return response()->json([
                'message' => 'Área actualizada exitosamente',
                'area' => $area,
                'status' => 200,
            ], 200);

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
