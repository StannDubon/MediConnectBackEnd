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
        $reference = AreaDoctor::all();
        if($reference->isEmpty()){
            return response()->json([
                'message' => 'No se encontraron referencias existentes',
                'status' => 200,
            ], 200);
        }
        return response()->json([
            'areas_doctores' => $reference,
            'status' => 200,
        ], 200);
    }

    public function show($id)
    {
        $reference = AreaDoctor::find($id);
        if (!$reference) {
            return response()->json([
                'message' => 'Referencia no encontrada',
                'status' => 404,
            ], 404);
        }
        return response()->json([
            'area_doctor' => [
                'id' => $reference->id,
                'area_id' => $reference->area_id,
                'doctor_id' => $reference->doctor_id
            ],
            'status' => 200,
        ], 200);
    }

    public function store(Request $request): JsonResponse{
        $validated = $request->validate([
            'area_id' => 'integer|required|exists:areas,id',
            'doctor_id' => 'integer|required|exists:doctores,id',
        ]);

        $data = AreaDoctor::create($validated);

        return response()->json([
            'message' => 'Referencia creada exitosamente',
            'area_doctor' => $data,
            'status' => 200,
        ], 201);
    }

    public function destroy($id){
        $data = AreaDoctor::find($id);
        if(!$data){
            return response()->json([
                'message' => 'Referencia no encontrada',
                'status' => 404,
            ], 404);
        }

        $data->delete();

        return response()->json([
            'message' => 'Referencia eliminada',
            'status' => 200,
        ], 200);
    }

    public function update(Request $request, $id): JsonResponse{
        $data = AreaDoctor::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Doctor no encontrado',
                'status' => 404,
            ], 404);
        }

        $validated = $request->validate([
            'area_id' => 'integer|required|exists:areas,id',
            'doctor_id' => 'integer|required|exists:doctores,id',
        ]);

        $data->area_id = $request->area_id;
        $data->doctor_id = $request->doctor_id;
        $data->save();

        return response()->json([
            'message' => 'Referencia actualizada exitosamente',
            'area_doctor' => $data,
            'status' => 200,
        ], 200);
    }

    public function updatePartial(Request $request, $id): JsonResponse{
        $data = AreaDoctor::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Doctor no encontrado',
                'status' => 404,
            ], 404);
        }

        if ($request->all() === []) {
            return response()->json([
                'message' => 'Por favor digite el campo que desea actualizar',
                'status' => 200,
            ], 200);
        }

        $validated = $request->validate([
            'area_id' => 'integer|exists:areas,id',
            'doctor_id' => 'integer|exists:doctores,id',
        ]);

        if($request->has('area_id')){
            $data->area_id = $request->area_id;
        }
        if($request->has('doctor_id')){
            $data->doctor_id = $request->doctor_id;
        }

        $data->save();

        return response()->json([
            'message' => 'Referencia actualizada exitosamente',
            'area_doctor' => $data,
            'status' => 200,
        ], 200);
    }
}
