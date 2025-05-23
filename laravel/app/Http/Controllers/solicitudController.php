<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paciente;
use App\Models\AreaDoctor;
use App\Models\Solicitud;

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

    public function show(Request $request){
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
}
