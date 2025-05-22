<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventario;
use Illuminate\Support\Facades\Validator;

class InventarioController extends Controller
{
    // Mostrar todos los inventarios
    public function index()
    {
        $inventario = Inventario::all();

        if ($inventario->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron medicamentos',
                'status' => 400
            ], 400);
        }

        return response()->json($inventario, 200);
    }

    // Crear un nuevo inventario
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|max:255',
            'cantidad' => 'required|integer'
        ]);

        // Si la validación falla, devolver los errores
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error en la validación de datos',
                'error' => $validator->errors(),
                'status' => 400,
            ], 400);
        }

        // Crear el nuevo inventario
        $inventario = Inventario::create([
            'nombre' => $request->nombre,
            'cantidad' => $request->cantidad
        ]);

        // Verificar si se pudo crear el inventario
        if (!$inventario) {
            return response()->json([
                'message' => 'Error al crear el item',
                'status' => 500
            ], 500);
        }

        // Respuesta exitosa
        return response()->json([
            'message' => 'Item creado exitosamente',
            'item' => $inventario,
            'status' => 201
        ], 201);
    }

    // Mostrar un inventario específico
    public function show($id)
    {
        $inventario = Inventario::find($id);

        if (!$inventario) {
            return response()->json([
                'message' => 'Inventario no encontrado',
                'status' => 404
            ], 404);
        }

        return response()->json($inventario, 200);
    }

    // Actualizar completamente un inventario
    public function update(Request $request, $id)
    {
        $inventario = Inventario::find($id);

        if (!$inventario) {
            return response()->json([
                'message' => 'Inventario no encontrado',
                'status' => 404
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|max:255',
            'cantidad' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error en la validación de datos',
                'error' => $validator->errors(),
                'status' => 400,
            ], 400);
        }

        $inventario->update([
            'nombre' => $request->nombre,
            'cantidad' => $request->cantidad
        ]);

        return response()->json([
            'message' => 'Item actualizado exitosamente',
            'item' => $inventario,
            'status' => 200
        ], 200);
    }

    // Actualizar parcialmente un inventario
    public function updatePartial(Request $request, $id)
    {
        $inventario = Inventario::find($id);

        if (!$inventario) {
            return response()->json([
                'message' => 'Inventario no encontrado',
                'status' => 404
            ], 404);
        }

        $validatedData = $request->only(['nombre', 'cantidad']);
        $validator = Validator::make($validatedData, [
            'nombre' => 'nullable|max:255',
            'cantidad' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error en la validación de datos',
                'error' => $validator->errors(),
                'status' => 400,
            ], 400);
        }

        $inventario->update(array_filter($validatedData));  // Solo actualiza los campos proporcionados

        return response()->json([
            'message' => 'Item parcialmente actualizado',
            'item' => $inventario,
            'status' => 200
        ], 200);
    }

    // Eliminar un inventario
    public function destroy($id)
    {
        $inventario = Inventario::find($id);

        if (!$inventario) {
            return response()->json([
                'message' => 'Inventario no encontrado',
                'status' => 404
            ], 404);
        }

        $inventario->delete();

        return response()->json([
            'message' => 'Item eliminado exitosamente',
            'status' => 200
        ], 200);
    }
}
