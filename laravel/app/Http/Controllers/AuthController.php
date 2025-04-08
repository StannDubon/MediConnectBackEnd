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

class AuthController extends Controller
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
            'clinica_diaria' => 'nullable|string',
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
            // Validar la solicitud
            $validator = Validator::make($request->all(), [
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            // Dependiendo del tipo, buscar al usuario correspondiente
            $user = null;
            if ($request->type == 'doctor') {
                $user = Doctor::where('email', $request->email)->first();
            } elseif ($request->type == 'patient') {
                $user = Paciente::where('email', $request->email)->first();
            } elseif ($request->type == 'admin') {
                $user = User::where('email', $request->email)->first();
            }

            // Si no se encuentra el usuario o la contraseña no es válida
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['error' => 'Credenciales inválidas.'], 401);
            }

            // Autenticar y generar el token para la cookie
            Auth::login($user);
            $token = $user->createToken('auth_token')->plainTextToken;

            // Dependiendo del tipo de usuario, devolver el nombre y apellido
            $userInfo = null;
            if ($request->type == 'doctor') {
                $userInfo = ['nombre' => $user->nombre, 'apellido' => $user->apellido];
            } elseif ($request->type == 'patient') {
                $userInfo = ['nombre' => $user->nombre, 'apellido' => $user->apellido];
            } elseif ($request->type == 'admin') {
                $userInfo = ['nombre' => $user->name, 'apellido' => $user->name];  // El modelo de admin podría tener "name" para ambos
            }

            // Retornar el mensaje con el nombre y apellido del usuario
            return response()->json([
                'message' => 'Login exitoso.',
                'token' => $token,
                'user' => $userInfo
            ]);
        }

        catch (\Exception $e) {
            return response()->json([
                'error' => 'Ocurrió un error inesperado.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    // Logout (cerrar sesión y eliminar token)
    public function logout(Request $request)
    {
        // Revoke el token para el logout
        $request->user()->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json(['message' => 'Logout exitoso.']);
    }



    // Obtener los datos del usuario autenticado
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
