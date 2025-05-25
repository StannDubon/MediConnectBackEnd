<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class pacienteController extends Controller
{
    public function index(){
        $paciente = Paciente::all();

        if($paciente->isEmpty()){
            return response()->json([
                'message' => 'No se encontraron pacientes',
                'status' => 400
            ], 400);
        }

        return response()->json($paciente, 200);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(),[
            'nombre' => 'required|max:255',
            'apellido' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Error en la validación de datos',
                'error' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        $paciente = Paciente::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido
        ]);

        $user = User::create([
            'name' => $request->nombre . ' ' . $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => 'patient',
            'paciente_id' => $paciente->id
        ]);

        if(!$paciente || !$user){
            return response()->json([
                'message' => 'Error al crear paciente',
                'status' => 500,
            ], 500);
        }

        return response()->json([
            'message' => 'Paciente creado exitosamente',
            'paciente' => $paciente,
            'usuario' => $user,
            'status' => 201
        ], 201);
    }

    public function show($id)
    {
        // Carga la relación user con el paciente
        $paciente = Paciente::with('user')->find($id);

        if(!$paciente) {
            return response()->json([
                'message' => 'Paciente no encontrado',
                'status' => 404
            ], 404);
        }

        // Verifica si tiene usuario relacionado
        if(!$paciente->user) {
            // Debug: Verifica qué usuario debería estar relacionado
            $user = User::where('paciente_id', $id)->first();

            return response()->json([
                'message' => 'Usuario asociado no encontrado (pero existe en users)',
                'debug' => [
                    'paciente_id' => $id,
                    'user_encontrado' => $user
                ],
                'status' => 404
            ], 404);
        }

        return response()->json([
            'paciente' => $paciente,
            'usuario' => $paciente->user,
            'status' => 200
        ], 200);
    }

    public function destroy($id)
    {
        $paciente = Paciente::find($id);

        if(!$paciente) {
            return response()->json([
                'message' => 'Paciente no encontrado',
                'status' => 404
            ], 404);
        }

        // First, check if there's an associated user
        if ($paciente->user) {
            // Remove the foreign key reference from the user
            $paciente->user->paciente_id = null;
            $paciente->user->save();
        }

        // Then delete the paciente
        $paciente->delete();

        return response()->json([
            'message' => 'Paciente eliminado',
            'status' => 200
        ], 200);
    }

    public function update(Request $request, $id){
        $paciente = Paciente::find($id);

        if(!$paciente){
            return response()->json([
                'message' => 'Paciente no encontrado',
                'status' => 404
            ], 404);
        }

        $validator = Validator::make($request->all(),[
            'nombre' => 'required|max:255',
            'apellido' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . optional($paciente->user)->id,
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Error en la validación de datos',
                'error' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        $paciente->nombre = $request->nombre;
        $paciente->apellido = $request->apellido;
        $paciente->save();

        if ($paciente->user) {
            $paciente->user->email = $request->email;
            $paciente->user->password = Hash::make($request->password);
            $paciente->user->name = $request->nombre . ' ' . $request->apellido;
            $paciente->user->save();
        }

        return response()->json([
            'message' => 'Paciente actualizado',
            'paciente' => $paciente,
            'status' => 200
        ], 200);
    }

    public function updatePartial(Request $request, $id){
        $paciente = Paciente::find($id);

        if(!$paciente){
            return response()->json([
                'message' => 'Paciente no encontrado',
                'status' => 404
            ], 404);
        }

        $validator = Validator::make($request->all(),[
            'nombre' => 'max:255',
            'apellido' => 'max:255',
            'email' => 'email|unique:users,email,' . optional($paciente->user)->id,
            'password' => 'min:2'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Error en la validación de datos',
                'error' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        if($request->has('nombre')){
            $paciente->nombre = $request->nombre;
        }
        if($request->has('apellido')){
            $paciente->apellido = $request->apellido;
        }

        $paciente->save();

        if($paciente->user){
            if($request->has('email')){
                $paciente->user->email = $request->email;
            }
            if($request->has('password')){
                $paciente->user->password = Hash::make($request->password);
            }
            if($request->has('nombre') || $request->has('apellido')){
                $paciente->user->name = ($request->nombre ?? $paciente->nombre) . ' ' . ($request->apellido ?? $paciente->apellido);
            }
            $paciente->user->save();
        }

        return response()->json([
            'message' => 'Paciente actualizado',
            'paciente' => $paciente,
            'status' => 200
        ], 200);
    }
}