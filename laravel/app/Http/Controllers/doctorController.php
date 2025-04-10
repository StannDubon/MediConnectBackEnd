<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class doctorController extends Controller
{
    public function index(){
        $doctores = Doctor::with(['user:id,email,doctor_id'])->get();

        if ($doctores->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron doctores existentes',
                'status' => 200,
            ], 200);
        }

        $doctores = $doctores->map(function ($doctor) {
            return [
                'id' => $doctor->id,
                'nombre' => $doctor->nombre,
                'apellido' => $doctor->apellido,
                'clinica_diaria' => $doctor->clinica_diaria,
                'imagen' => $doctor->imagen,
                'email' => $doctor->user->email ?? null,
            ];
        });

        return response()->json([
            'doctores' => $doctores,
            'status' => 200,
        ], 200);
    }

    public function show($id)
    {
        $doctor = Doctor::with(['user:id,email,doctor_id'])->find($id);

        if (!$doctor) {
            return response()->json([
                'message' => 'Doctor no encontrado',
                'status' => 404,
            ], 404);
        }

        $data = [
            'nombre' => $doctor->nombre,
            'apellido' => $doctor->apellido,
            'clinica_diaria' => $doctor->clinica_diaria,
            'imagen' => $doctor->imagen,
            'email' => $doctor->user->email ?? null,
        ];

        return response()->json([
            'doctor' => $data,
            'status' => 200,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {

        $validated = $request->validate([
            'nombre' => 'required|max:255',
            'apellido' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'clinica_diaria' => 'required|integer',
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $image = $request->file('imagen');
        $filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('images', $filename, 'public');

        $validated['imagen'] = $filename;

        $userData = [
            'name' => $validated['nombre'] . " " . $validated['apellido'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'type' => 'doctor',
        ];
        unset($validated['email']);
        unset($validated['password']);
        $doctor = Doctor::create($validated);

        $userData['doctor_id'] = $doctor->id;
        $user = User::create($userData);

        return response()->json([
            'message' => 'Doctor creado exitosamente',
            'doctor' => [
                'id' => $doctor->id,
                'nombre' => $doctor->nombre,
                'apellido' => $doctor->apellido,
                'clinica_diaria' => $doctor->clinica_diaria,
                'imagen' => $doctor->imagen,
                'email' => $doctor->user->email ?? null,
            ],
            'status' => 201,
        ], 201);
    }

    public function destroy($id)
    {
        $doctor = Doctor::find($id);
        if (!$doctor) {
            return response()->json([
                'message' => 'Doctor no encontrado',
                'status' => 404,
            ], 404);
        }

        if ($doctor->user) {
            $doctor->user->delete();
        }
        $doctor->delete();
        return response()->json([
            'message' => 'Doctor eliminado correctamente',
            'status' => 200,
        ], 200);
    }

    public function update(Request $request, $id): JsonResponse{
        $doctor = Doctor::with(['user:id,email,doctor_id'])->find($id);

        if (!$doctor) {
            return response()->json([
                'message' => 'Doctor no encontrado',
                'status' => 404,
            ], 404);
        }

        $validated = $request->validate([
            'nombre' => 'required|max:255',
            'apellido' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $doctor->user->id,
            'password' => 'required',
            'clinica_diaria' => 'required|integer',
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $image = $request->file('imagen');
        $filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('images', $filename, 'public');

        Storage::disk('public')->delete('images/' . $doctor->imagen);

        $doctor->nombre = $request->nombre;
        $doctor->apellido = $request->apellido;
        $doctor->clinica_diaria = $request->clinica_diaria;
        $doctor->imagen = $filename;

        $doctor->user->email = $request->email;
        $doctor->user->password = $request->password;

        $doctor->user->save();
        $doctor->save();

        return response()->json([
            'message' => 'Doctor actualizado exitosamente',
            'doctor' => [
                'id' => $doctor->id,
                'nombre' => $doctor->nombre,
                'apellido' => $doctor->apellido,
                'clinica_diaria' => $doctor->clinica_diaria,
                'imagen' => $doctor->imagen,
                'email' => $doctor->user->email ?? null,
            ],
            'status' => 200,
        ], 200);
    }

    public function updatePartial(Request $request, $id): JsonResponse{
        $doctor = Doctor::with(['user:id,email,doctor_id'])->find($id);

        if (!$doctor) {
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
            'nombre' => 'max:255',
            'apellido' => 'max:255',
            'email' => 'email|unique:users,email,' . $doctor->user->id,
            'password' => 'min:2',
            'clinica_diaria' => 'integer',
            'imagen' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if($request->has('nombre')){
            $doctor->nombre = $request->nombre;
        }
        if($request->has('apellido')){
            $doctor->apellido = $request->apellido;
        }
        if($request->has('clinica_diaria')){
            $doctor->clinica_diaria = (int)$request->clinica_diaria;
        }
        if($request->has('imagen')){
            $image = $request->file('imagen');
            $filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('images', $filename, 'public');
            Storage::disk('public')->delete('images/' . $doctor->imagen);
            $doctor->imagen = $filename;
        }

        if($request->has('email')){
            $doctor->user->email = $request->email;
        }
        if($request->has('password')){
            $doctor->user->password = $request->password;
        }

        $doctor->user->save();
        $doctor->save();

        return response()->json([
            'message' => 'Doctor actualizado exitosamente',
            'doctor' => [
                'id' => $doctor->id,
                'nombre' => $doctor->nombre,
                'apellido' => $doctor->apellido,
                'clinica_diaria' => $doctor->clinica_diaria,
                'imagen' => $doctor->imagen,
                'email' => $doctor->user->email ?? null,
            ],
            'status' => 200,
        ], 200);
    }

    public function indexWithAreas()
    {
        // Obtener todos los doctores con sus áreas
        $doctores = Doctor::with('areas')->get();

        if ($doctores->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron doctores',
                'status' => 200,
            ], 200);
        }

        // Formatear la respuesta
        $resultado = $doctores->map(function ($doctor) {
            return [
                'doctor_id' => $doctor->id,
                'nombre_doctor' => $doctor->nombre . ' ' . $doctor->apellido,
                'areas_asignadas' => $doctor->areas->pluck('nombre')->toArray()
            ];
        });

        return response()->json([
            'areas_doctores' => $resultado,
            'status' => 200
        ], 200);
    }
}
