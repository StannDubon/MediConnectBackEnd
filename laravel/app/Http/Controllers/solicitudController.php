<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paciente;
use App\Models\AreaDoctor;
use App\Models\Solicitud;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class solicitudController extends Controller
{
    public function index(){
        $solicitud = Solicitud::all();

        if($solicitud->isEmpty()){
            return response()->json([
                'message' => 'No se encontraron solicitudes',
                'status' => 400
            ], 400);
        }

        return response()->json($solicitud, 200);
    }

    public function store(Request $request){
        $validated = $request->validate([
            'areas_doctores_id' => 'integer|required|exists:areas_doctores,id',
            'pacientes_id' => 'integer|required|exists:pacientes,id',
            'motivo' => 'required|string|max:255'
        ]);

        $data = Solicitud::create($validated);

        return response()->json([
            'message' => 'Solicitud enviada exitosamente',
            'solicitud' => $data,
            'status' => 200,
        ], 201);
    }

    public function show($id){
        $solicitud = Solicitud::find($id);

        if (!$solicitud) {
            return response()->json([
                'message' => 'Solicitud no encontrado',
                'status' => 404
            ], 404);
        }

        return response()->json($solicitud, 200);
    }

    public function updateFromPatient(Request $request, $id)
    {
        try {
            // Usar findOrFail para lanzar ModelNotFoundException automáticamente
            $solicitud = Solicitud::findOrFail($id);
            
            // Verificar que el paciente es el dueño de la solicitud
            $user = auth()->user();
            if($user->type !== 'admin' && $solicitud->pacientes_id != $user->paciente_id) {
                return response()->json([
                    'message' => 'No autorizado para actualizar esta solicitud',
                    'status' => 403,
                ], 403);
            }
    
            $validated = $request->validate([
                'areas_doctores_id' => 'integer|required|exists:areas_doctores,id',
                'motivo' => 'required|string|max:255'
            ]);
    
            $solicitud->update($validated);
    
            return response()->json([
                'message' => 'Solicitud actualizada exitosamente',
                'solicitud' => $solicitud,
                'status' => 200,
            ], 200);
    
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Solicitud no encontrada',
                'status' => 404,
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $e->errors(),
                'status' => 422,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error del servidor',
                'error' => $e->getMessage(), // Solo en desarrollo
                'status' => 500,
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Usar findOrFail para lanzar ModelNotFoundException automáticamente
            $solicitud = Solicitud::findOrFail($id);
            
    
            $validated = $request->validate([
                'areas_doctores_id' => 'integer|required|exists:areas_doctores,id',
                'motivo' => 'required|string|max:255',
                'notas' => 'required|string|max:255',
            ]);
    
            $solicitud->update($validated);
    
            return response()->json([
                'message' => 'Solicitud actualizada exitosamente',
                'solicitud' => $solicitud,
                'status' => 200,
            ], 200);
    
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Solicitud no encontrada',
                'status' => 404,
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar los datos',
                'errors' => $e->errors(),
                'status' => 422,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error del servidor',
                'error' => $e->getMessage(), // Solo en desarrollo
                'status' => 500,
            ], 500);
        }
    }
}
