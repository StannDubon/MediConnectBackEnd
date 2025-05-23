<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paciente;
use App\Models\Examenes;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class examenesController extends Controller
{

    public function index()
    {
        return Examenes::with('paciente')->get();
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha' => 'required|date',
        ]);

        $examen = Examenes::create($validated);

        return response()->json([
            'message' => 'Examen creado exitosamente',
            'examen' => $examen
        ], 201);
    }

    public function show($id)
    {
        $examen = Examenes::with('paciente')->find($id);

        if (!$examen) {
            return response()->json(['message' => 'Examen no encontrado'], 404);
        }

        return $examen;
    }

    // Actualizar un examen
    public function update(Request $request, $id)
    {
        $examen = Examenes::find($id);

        if (!$examen) {
            return response()->json(['message' => 'Examen no encontrado'], 404);
        }

        $validated = $request->validate([
            'titulo' => 'string|max:255',
            'descripcion' => 'nullable|string',
            'fecha' => 'date',
        ]);

        $examen->update($validated);

        return response()->json([
            'message' => 'Examen actualizado',
            'examen' => $examen
        ]);
    }

    // Eliminar un examen
    public function destroy($id)
    {
        $examen = Examenes::find($id);

        if (!$examen) {
            return response()->json(['message' => 'Examen no encontrado'], 404);
        }

        $examen->delete();

        return response()->json(['message' => 'Examen eliminado']);
    }
}
