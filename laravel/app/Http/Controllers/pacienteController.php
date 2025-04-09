<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paciente;
use Illuminate\Support\Facades\Validator;

class pacienteController extends Controller
{
    public function index(){
        $paciente = Paciente::all();

        if($paciente->IsEmpty()){
            $data = [
                'message' => 'No se encontraron pacientes',
                'status' => 400
            ];

            return response()->json($data, 400);
        }

        return response()->json($paciente, 200);
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(),[
            'nombre' => 'required|max:255',
            'apellido' => 'required|max:255'
        ]);

        if($validator->fails()){
            $data = [
                'message' => 'Error en la validación de datos',
                'error' => $validator->errors(),
                'status' => 400
            ];

            return response()->json($data, 400);
        }

        $paciente = Paciente::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido
        ]);

        if(!$paciente){
            $data = [
                'message' => 'Error al crear paciente',
                'status' => 500,
            ];

            return response()->json($data, 500);
        }

        $data = [
            'message' => $paciente,
            'status' => 201
        ];

        return response()->json($data, 201);
    }

    public function show($id){
        $paciente = Paciente::find($id);

        if(!$paciente){
            $data = [
                'message' => 'Paciente no encontrado',
                'status' => 404
            ];

            return response()->json($data, 404);
        }

        $data = [
            'student' => $paciente,
            'status' => 200
        ];        

        return response()->json($data, 200);
    }

    public function destroy($id){
        $paciente = Paciente::find($id);

        if(!$paciente){
            $data = [
                'message' => 'Paciente no encontrado',
                'status' => 200
            ];

            return response()->json($data, 200);
        }

        $paciente->delete();

        $data = [
            'message' => 'Paciente eliminado',
            'status' => 200
        ];

        return response()->json($data, 200);
    }

    public function update(Request $request,$id){
        $paciente = Paciente::find($id);

        if(!$paciente){
            $data = [
                'message' => 'Paciente no encontrado',
                'status' => 200
            ];

            return response()->json($data, 200);
        }

        $validator = Validator::make($request->all(),[
            'nombre' => 'required|max:255',
            'apellido' => 'required|max:255'
        ]);

        if($validator->fails()){
            $data = [
                'message' => 'Error en la validación de datos',
                'error' => $validator->errors(),
                'status' => 400
            ];

            return response()->json($data, 400);
        }

        $paciente->nombre = $paciente->nombre;
        $paciente->apellido = $request->apellido;

        $paciente->save();

        $data = [
            'message' => 'Paciente actualizado',
            'student' => $paciente,
            'status' => 200
        ];

        return response()->json($data, 200);
    }

    public function updatePartial(Request $request, $id){

        $paciente = Paciente::find($id);

        if(!$paciente){
            $data = [
                'message' => 'Paciente no encontrado',
                'status' => 200
            ];

            return response()->json($data, 200);
        }

        $validator = Validator::make($request->all(),[
            'nombre' => 'required|max:255',
            'apellido' => 'required|max:255'
        ]);

        if($validator->fails()){
            $data = [
                'message' => 'Error en la validación de datos',
                'error' => $validator->errors(),
                'status' => 400
            ];

            return response()->json($data, 400);
        }

        if($request->has('nombre')){
            $paciente->nombre = $request->nombre;
        }
        if($request->has('apellido')){
            $paciente->apellido = $request->apellido;
        }

        $paciente->save();

        $data = [
            'message' => 'Estudiante actualizado',
            'student' => $paciente,
            'status' => 200
        ];

        return response()->json($data, 200);
    }
}
