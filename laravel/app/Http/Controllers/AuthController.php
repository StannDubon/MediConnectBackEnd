<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Paciente;
use App\Models\Doctor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class authController extends Controller
{
    public function signupAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => 'admin',
        ]);

        return response()->json(['message' => 'Admin registrado', 'user' => $user], 201);
    }

    public function signupDoctor(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'clinica_diaria' => 'required|integer',
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $image = $request->file('imagen');
        $filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('images', $filename, 'public');

        $doctor = Doctor::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'clinica_diaria' => $request->clinica_diaria,
            'imagen' => $filename,
        ]);

        $user = User::create([
            'name' => $request->nombre . ' ' . $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => 'doctor',
            'doctor_id' => $doctor->id,
        ]);

        $user['imagen'] = $filename;
        unset($user['id']);

        return response()->json(['message' => 'Doctor registrado', 'user' => $user], 201);
    }

    public function signupPatient(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $paciente = Paciente::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
        ]);

        $user = User::create([
            'name' => $request->nombre . ' ' . $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => 'patient',
            'paciente_id' => $paciente->id,
        ]);

        return response()->json(['message' => 'Paciente registrado', 'user' => $user], 201);
    }





    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['error' => 'Credenciales inválidas.'], 401);
            }

            Auth::login($user);

            $abilities = match ($user->type) {
                'admin'   => ['server-admin'],
                'doctor'  => ['server-doctor'],
                'patient' => ['server-patient'],
                default   => ['server-unknown'],
            };
            $token = $user->createToken('auth_token', $abilities)->plainTextToken;

            $userInfo = null;
            switch ($user->type) {
                case 'admin':
                    $userInfo = [
                        'id' => $user->id,
                        'nombre' => $user->name,
                        'apellido' => $user->name,
                        'type' => 'admin',
                    ];
                    break;
                case 'doctor':
                    $doctor = \App\Models\Doctor::find($user->doctor_id);
                    if (!$doctor) {
                        return response()->json(['error' => 'Doctor no encontrado.'], 404);
                    }
                    $userInfo = [
                        'id' => $user->id,
                        'doctor_id' => $doctor->id,
                        'nombre' => $doctor->nombre,
                        'apellido' => $doctor->apellido,
                        'clinica_diaria' => $doctor->clinica_diaria,
                        'imagen' => $doctor->imagen,
                        'type' => 'doctor',
                    ];
                    break;

                case 'patient':
                    $paciente = \App\Models\Paciente::find($user->paciente_id);
                    if (!$paciente) {
                        return response()->json(['error' => 'Paciente no encontrado.'], 404);
                    }
                    $userInfo = [
                        'id' => $user->id,
                        'paciente_id' => $paciente->id,
                        'nombre' => $paciente->nombre,
                        'apellido' => $paciente->apellido,
                        'type' => 'patient',
                    ];
                    break;

                default:
                    return response()->json(['error' => 'Tipo de usuario desconocido.'], 400);
            }

            return response()->json([
                'message' => 'Login exitoso.',
                'token' => $token,
                'user' => $userInfo,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ocurrió un error inesperado.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.'
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user();
        $userInfo = [
            'type' => $user->type,  // Tipo de usuario (admin, doctor, patient)
        ];

        switch ($user->type) {
            case 'admin':
                $userInfo['nombre'] = $user->name;
                $userInfo['apellido'] = implode(' ', array_slice(explode(' ', $user->name), 1));
                break;

            case 'doctor':
                $doctor = \App\Models\Doctor::find($user->doctor_id);
                if ($doctor) {
                    $userInfo['id'] = $doctor->id;
                    $userInfo['nombre'] = $doctor->nombre;
                    $userInfo['apellido'] = $doctor->apellido;
                    $userInfo['clinica_diaria'] = $doctor->clinica_diaria;
                    $userInfo['imagen'] = $doctor->imagen;
                }
                break;

            case 'patient':
                $paciente = \App\Models\Paciente::find($user->paciente_id);
                if ($paciente) {
                    $userInfo['id'] = $paciente->id;
                    $userInfo['nombre'] = $paciente->nombre;
                    $userInfo['apellido'] = $paciente->apellido;
                }
                break;
        }

        // Retornar la información completa del usuario
        return response()->json($userInfo);
    }

}
